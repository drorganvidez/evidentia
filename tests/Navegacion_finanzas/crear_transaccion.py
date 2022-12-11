from typing_extensions import Self
import unittest
from selenium import webdriver
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
import time


class Suite(unittest.TestCase):
    
    def click_element(driver, element):
            time.sleep(1)
            WebDriverWait(driver, 2)\
                .until(EC.element_to_be_clickable((By.XPATH, element))).click()

    def send_keys(driver, element, keys):
        time.sleep(1)
        WebDriverWait(driver, 2)\
            .until(EC.element_to_be_clickable((By.XPATH, element))).send_keys(keys)

    input_login = '/html/body/div[1]/div[2]/div/form/div[2]/input'
    input_password = '/html/body/div[1]/div[2]/div/form/div[3]/input'
    input_submit = '/html/body/div[1]/div[2]/div/form/div[5]/div[2]/button'
    crear_incidencia = '/html/body/div[1]/aside[1]/div/nav[2]/ul/li[4]/a/p'
    input_motivo = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[1]/input'
    input_cantidad = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[2]/input'
    click_tipo = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[3]/div/button/div/div/div'
    seleccionar_tipo = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[3]/div/div/div/ul/li[1]/a/span'
    click_comite = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[4]/div/button/div/div/div'
    seleccionar_comite = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[4]/div/div/div/ul/li[6]/a/span'
    submit_transaccion = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[5]/button'
    input_submit_incidencia_confirm = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[6]/div/div/div[3]/button[2]'
    incidencia_con_exito = '/html/body/div[2]/div/div[2]'

    options = webdriver.ChromeOptions()
    driver = webdriver.Chrome(options=options)
    driver.get("http://localhost/21/login")
    driver.maximize_window()
    time.sleep(2)

    #Login
    send_keys(driver, input_login, 'coordinador1')
    send_keys(driver, input_password, 'coordinador1')
    click_element(driver, input_submit)

    #Crear incidencia
    click_element(driver, crear_incidencia)
    send_keys(driver, input_motivo, 'Titulo de prueba selenium')
    send_keys(driver, input_cantidad, '50')
    click_element(driver, click_tipo)
    click_element(driver, seleccionar_tipo)
    click_element(driver, click_comite)
    click_element(driver, seleccionar_comite)
    click_element(driver, submit_transaccion)
    click_element(driver, input_submit_incidencia_confirm)
    
    if(driver.find_element("xpath", incidencia_con_exito).text == 'Transacción creada con éxito.'):
        print('----------------------------------------------------------')
        print('1. TEST EJECUTADO CON ÉXITO');
        print('----------------------------------------------------------')
    else:
        print('----------------------------------------------------------')
        print('1. TEST FALLIDO');
        print('----------------------------------------------------------')

    