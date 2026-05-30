param()
if (-not $env:MODEL_URL) {
    Write-Error "ERROR: MODEL_URL environment variable is not set"
    exit 1
}

New-Item -ItemType Directory -Force -Path ml_api | Out-Null
Write-Output "Downloading model from $env:MODEL_URL to ml_api\model_elec.pkl..."
Invoke-WebRequest -Uri $env:MODEL_URL -OutFile "ml_api\model_elec.pkl" -UseBasicParsing
Write-Output "Download complete."
