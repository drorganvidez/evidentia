python -m venv %TEMP%\venvidentia\
call %TEMP%\venvidentia\Scripts\activate.bat
python -m pip --version
python -m pip install --upgrade pip
python -m pip install -r requirements.txt
python -m unittest discover tests\TestSuiteVista\ "*_test.py"