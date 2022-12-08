python -m venv /tmp/venvidentia/
source /tmp/venvidentia/bin/activate
pip install --upgrade pip
pip install -r requirements.txt
python -m unittest discover tests/TestSuiteVista/ "*_test.py"