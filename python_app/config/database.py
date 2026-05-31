import pymysql, pymysql.cursors
from config.config import Config

_conn = None

def get_db():
    global _conn
    try:
        if _conn is None or not _conn.open:
            raise Exception("reconnect")
        _conn.ping(reconnect=True)
    except:
        _conn = pymysql.connect(
            host=Config.DB_HOST, port=Config.DB_PORT,
            user=Config.DB_USER, password=Config.DB_PASSWORD,
            database=Config.DB_NAME, charset="utf8mb4",
            cursorclass=pymysql.cursors.DictCursor, autocommit=True
        )
    return _conn
