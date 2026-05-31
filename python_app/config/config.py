import os
from dotenv import load_dotenv
load_dotenv()

class Config:
    ENV          = os.getenv("APP_ENV", "development")
    SECRET_KEY   = os.getenv("APP_SECRET_KEY", "dev-secret-key")
    BASE_URL     = os.getenv("APP_BASE_URL", "http://localhost:5000")
    DEBUG        = ENV == "development"
    DB_HOST      = os.getenv("DB_HOST", "localhost")
    DB_PORT      = int(os.getenv("DB_PORT", 3306))
    DB_NAME      = os.getenv("DB_NAME", "suivi_colis_sae")
    DB_USER      = os.getenv("DB_USER", "root")
    DB_PASSWORD  = os.getenv("DB_PASSWORD", "root")
    CAS_HOST     = os.getenv("CAS_HOST", "cas.univ-paris13.fr")
    CAS_CONTEXT  = os.getenv("CAS_CONTEXT", "/cas/")
    CAS_PORT     = int(os.getenv("CAS_PORT", 443))
    ADMIN_UIDS   = [u.strip() for u in os.getenv("ADMIN_UIDS","").split(",") if u.strip()]
    SESSION_TYPE = "filesystem"
