
import sys
import os

# Add current directory to path so we can import 'app'
sys.path.append(os.getcwd())

try:
    print("Attempting to import base_dash router...")
    from app.dashboards.base_dash.router import router
    print("SUCCESS: base_dash router imported!")
except Exception as e:
    print(f"CRITICAL FAILURE: {e}")
    import traceback
    traceback.print_exc()
