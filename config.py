import os

class Config:
    DATABASE       = "app/database/database.db"
    DEBUG          = True
    SECRET_KEY     = os.getenv("SECRET_KEY", "change-moi-en-production")
    JWT_SECRET_KEY = os.getenv("JWT_SECRET_KEY", "jwt-secret-change-moi")
