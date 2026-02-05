import psycopg2
import os

DB_HOST = '192.168.0.31'
DB_NAME = 'agentic'
DB_USER = 'acartin'
DB_PASS = 'Toyota_15'

tables = [
    'lead_clients', 'lead_properties', 'lead_property_images', 
    'lead_brand_configs', 'lead_appointments', 'lead_leads'
]

try:
    conn = psycopg2.connect(host=DB_HOST, database=DB_NAME, user=DB_USER, password=DB_PASS)
    cur = conn.cursor()
    for t in tables:
        print(f"\nTable: {t}")
        cur.execute(f"SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '{t}'")
        cols = cur.fetchall()
        for c in cols:
            print(f"  {c[0]}: {c[1]}")
    cur.close()
    conn.close()
except Exception as e:
    print(f"Error: {e}")
