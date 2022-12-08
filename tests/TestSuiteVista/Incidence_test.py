from typing_extensions import Self
import unittest
from selenium import webdriver
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
import time


#Xpath elements
input_login = '/html/body/div[1]/div[2]/div/form/div[2]/input'
input_password = '/html/body/div[1]/div[2]/div/form/div[3]/input'
input_submit = '/html/body/div[1]/div[2]/div/form/div[5]/div[2]/button'
crear_incidencia = '/html/body/div[1]/aside[1]/div/nav[2]/ul/li[6]/a/p'
input_titulo = '/html/body/div[1]/div[1]/section/div/form/div/div[1]/div/div/div/div[1]/input'
input_incidencia = '/html/body/div[1]/div[1]/section/div/form/div/div[1]/div/div/div/div[3]/div/div[3]/div[2]'
input_submit_incidencia = '/html/body/div[1]/div[1]/section/div/form/div/div[1]/div/div/div/div[4]/button'
input_submit_incidencia_confirm = '/html/body/div[1]/div[1]/section/div/form/div/div[1]/div/div/div/div[5]/div/div/div[3]/button[2]'
incidencia_con_exito = '/html/body/div[2]/div/div[2]'

input_titulo_corto = '/html/body/div[1]/div[1]/section/div/form/div/div[1]/div/div/div/div[1]/span/strong'
input_incidencia_corta = '/html/body/div[1]/div[1]/section/div/form/div/div[1]/div/div/div/div[3]/span/strong'
#Driver options



#Functions to click and send keys
def click_element(driver, element):
    time.sleep(1)
    WebDriverWait(driver, 2)\
        .until(EC.element_to_be_clickable((By.XPATH, element))).click()

def send_keys(driver, element, keys):
    time.sleep(1)
    WebDriverWait(driver, 2)\
        .until(EC.element_to_be_clickable((By.XPATH, element))).send_keys(keys)
        

class Suite(unittest.TestCase):

    def test_create_incidence_positive(self):
        #Driver options
        options = webdriver.ChromeOptions()
        #options.add_argument('--headless')
        driver = webdriver.Chrome(options=options)
        driver.get("http://localhost/21/login")
        driver.maximize_window()
        time.sleep(2)

        #Login
        send_keys(driver, input_login, 'alumno1')
        send_keys(driver, input_password, 'alumno1')
        click_element(driver, input_submit)

        #Crear incidencia
        click_element(driver, crear_incidencia)
        send_keys(driver, input_titulo, 'Titulo de prueba')
        send_keys(driver, input_incidencia, 'Incidencia de prueba')
        click_element(driver, input_submit_incidencia)
        click_element(driver, input_submit_incidencia_confirm)

        #Comprobar que se ha creado la incidencia
        self.assertEqual(driver.find_element(By.XPATH,incidencia_con_exito).text, 'Incidencia creada con éxito.')
        driver.quit()
    
    def test_create_incidence_negative(self):
        #Driver options
        options = webdriver.ChromeOptions()
        #options.add_argument('--headless')
        driver = webdriver.Chrome(options=options)
        driver.get("http://localhost/21/login")
        driver.maximize_window()
        time.sleep(2)

        #Login
        send_keys(driver, input_login, 'alumno1')
        send_keys(driver, input_password, 'alumno1')
        click_element(driver, input_submit)

        #Crear incidencia
        click_element(driver, crear_incidencia)
        #Titulo demasiado corto
        send_keys(driver, input_titulo, 'tit')
        #Incidencia demasiado corta
        send_keys(driver, input_incidencia, 'In')

        click_element(driver, input_submit_incidencia)
        click_element(driver, input_submit_incidencia_confirm)
        

        #Comprobar que se ha creado la incidencia
        self.assertEqual(driver.find_element(By.XPATH,input_titulo_corto).text, 'título debe contener al menos 5 caracteres.')
        self.assertEqual(driver.find_element(By.XPATH,input_incidencia_corta).text, 'Debe tener un mínimo de 10 caracteres.')
        driver.quit()

if __name__ == '__main__':
    unittest.main()