from flask import Flask, request, jsonify
import os
import joblib
import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import train_test_split
import pickle
from huggingface_hub import hf_hub_download

MODEL_FILE = os.environ.get('MODEL_FILE', os.path.join(os.path.dirname(__file__), 'model_elec.pkl'))
DEFAULT_MODEL_FILE = os.path.join(os.path.dirname(__file__), 'model_rf.pkl')
SCALER_FILE = os.environ.get('SCALER_FILE', os.path.join(os.path.dirname(__file__), 'scaler_elec.pkl'))
DEFAULT_SCALER_FILE = os.path.join(os.path.dirname(__file__), 'scaler_rf.pkl')
MODEL_PATH = "model_elec.pkl"

app = Flask(__name__)

LOADED_SCALER = None

def load_model():
    # Si le .pkl est déjà présent localement
    if os.path.exists(MODEL_PATH):
        print("📦 Chargement du modèle local...")
        with open(MODEL_PATH, "rb") as f:
            return pickle.load(f)

    # Sinon téléchargement depuis Hugging Face
    print("📥 Téléchargement depuis Hugging Face...")
    path = hf_hub_download(
        repo_id=os.environ.get("HF_REPO_ID"),
        filename="model.pkl",
        token=os.environ.get("HF_TOKEN")
    )
    with open(path, "rb") as f:
        return pickle.load(f)

model = load_model()
print("✅ Modèle chargé avec succès")

def resolve_file(primary, fallback=None):
    if os.path.exists(primary):
        return primary
    if fallback and os.path.exists(fallback):
        return fallback
    return None


def resolve_model_path():
    return resolve_file(MODEL_FILE, DEFAULT_MODEL_FILE)


def resolve_scaler_path():
    return resolve_file(SCALER_FILE, DEFAULT_SCALER_FILE)


def build_raw_features(df):
    # Create derived time features and preserve raw columns
    df = df.copy()
    df['mois'] = df['mois'].astype(float).fillna(0)
    df['mois_sin'] = np.sin(2 * np.pi * df['mois'] / 12)
    df['mois_cos'] = np.cos(2 * np.pi * df['mois'] / 12)
    return df


def build_features(df):
    df = build_raw_features(df)
    scaler_path = resolve_scaler_path()
    if scaler_path is not None:
        scaler = joblib.load(scaler_path)
        return scaler.transform(df)

    # Fallback raw numeric feature matrix with categorical encoding
    if 'type_equipement' in df.columns:
        df['type_equipement'] = df['type_equipement'].astype(str).fillna('unknown')
    if 'type_utilisateur' in df.columns:
        df['type_utilisateur'] = df['type_utilisateur'].astype(str).fillna('Particulier')
    df['type_equipement_id'] = df['type_equipement'].astype('category').cat.codes
    df['type_utilisateur_id'] = df['type_utilisateur'].map({'Particulier': 0, 'Entreprise': 1}).fillna(0)

    cols = ['puissance_w', 'heures_utilisation_jour', 'jours_utilisation_mois', 'anciennete_equipement_ans',
            'nombre_utilisateurs', 'mois_sin', 'mois_cos', 'conso_mois_precedent_kwh',
            'conso_moyenne_3mois_kwh', 'type_equipement_id', 'type_utilisateur_id']
    return df[cols].astype(float).fillna(0).values


def ensure_model():
    model_path = resolve_model_path()
    if model_path is not None:
        return joblib.load(model_path)

    # Train a simple RandomForest on synthetic data
    rng = np.random.RandomState(42)
    n = 2000
    puissance = rng.uniform(10, 3000, n)
    heures = rng.uniform(0.5, 12, n)
    jours = rng.randint(1,30, n)
    anciennete = rng.randint(0,10, n)
    nb_users = rng.randint(1,10, n)
    mois = rng.randint(1,13, n)
    conso_prev = (puissance/1000) * heures * jours * (1 + 0.02*anciennete) * (0.8 + rng.rand(n)*0.4)
    conso_moy3 = conso_prev * (0.95 + rng.rand(n)*0.1)
    type_ids = rng.randint(0,5,n)
    user_ids = rng.randint(0,2,n)

    X = np.column_stack([puissance, heures, jours, anciennete, nb_users, mois, conso_prev, conso_moy3, type_ids, user_ids])
    seasonal = np.where(np.isin(mois, [6,7,8,9]), 1.15, 1.0)
    y = conso_prev * seasonal * (0.9 + rng.rand(n)*0.3)

    model = RandomForestRegressor(n_estimators=100, n_jobs=-1, random_state=42)
    model.fit(X, y)
    model_path = MODEL_FILE if MODEL_FILE.endswith('.plk') else DEFAULT_MODEL_FILE
    joblib.dump(model, model_path)
    return model


@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status':'ok'})


@app.route('/predict/total', methods=['POST'])
def predict_total():
    payload = request.get_json() or {}
    recepteurs = payload.get('recepteurs') or payload.get('receivers') or []
    if not isinstance(recepteurs, list):
        return jsonify({'error':'Invalid payload, recepteurs must be a list'}), 400

    df = pd.DataFrame(recepteurs)
    # Ensure expected columns exist
    for c in ['puissance_w','heures_utilisation_jour','jours_utilisation_mois','anciennete_equipement_ans',
              'nombre_utilisateurs','mois','conso_mois_precedent_kwh','conso_moyenne_3mois_kwh','type_equipement','type_utilisateur']:
        if c not in df.columns:
            df[c] = 0

    model = ensure_model()
    X = build_features(df)
    preds = model.predict(X)

    recep_results = []
    total_kwh = 0.0
    total_current = 0.0
    for i, r in df.iterrows():
        kwh = float(round(preds[i], 4))
        total_kwh += kwh
        cur = float(r.get('conso_mois_precedent_kwh', 0) or 0)
        total_current += cur
        recep_results.append({
            'id': int(r.get('id') or 0),
            'nom': str(r.get('nom') or ''),
            'type': str(r.get('type_equipement') or ''),
            'kwh_predit': kwh,
        })

    variation = round((total_kwh - total_current) / total_current * 100, 2) if total_current > 0 else None

    return jsonify({
        'succes': True,
        'data': {
            'total_kwh': round(total_kwh, 4),
            'variation': variation,
            'recepteurs': recep_results,
            'nb_recepteurs': len(recep_results),
        }
    })


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
