#!/usr/bin/env bash
set -euo pipefail

if [ -z "${MODEL_URL:-}" ]; then
  echo "ERROR: MODEL_URL environment variable is not set"
  exit 1
fi

mkdir -p ml_api
echo "Downloading model from $MODEL_URL to ml_api/model_elec.pkl..."
curl -fSL "$MODEL_URL" -o ml_api/model_elec.pkl
echo "Download complete."
