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

    
    def test_denegar_transaction(self):

        def click_element(driver, element):
            time.sleep(1)
            WebDriverWait(driver, 2)\
                .until(EC.element_to_be_clickable((By.XPATH, element))).click()

        def send_keys(driver, element, keys):
            time.sleep(1)
            WebDriverWait(driver, 2)\
                .until(EC.element_to_be_clickable((By.XPATH, element))).send_keys(keys)



        options = webdriver.ChromeOptions()
        driver = webdriver.Chrome(options=options)
        input_login = '/html/body/div[1]/div[2]/div/form/div[2]/input'
        input_password = '/html/body/div[1]/div[2]/div/form/div[3]/input'
        input_submit = '/html/body/div[1]/div[2]/div/form/div[5]/div[2]/button'
        crear_transaccion = '/html/body/div[1]/aside[1]/div/nav[2]/ul/li[5]/a'
        input_motivo = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[1]/input'
        input_cantidad = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[2]/input'
        click_tipo = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[3]/div/button/div/div/div'
        seleccionar_tipo = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[3]/div/div/div/ul/li[1]/a/span'
        click_comite = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[4]/div/button/div/div/div'
        seleccionar_comite = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[4]/div/div/div/ul/li[6]/a/span'
        submit_transaccion = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[5]/button'
        input_submit_incidencia_confirm = '/html/body/div[1]/div[1]/section/div/form/div/div/div/div/div/div[6]/div/div/div[3]/button[2]'
        incidencia_con_exito = '/html/body/div[2]/div/div[2]'
        cerrar_aviso = '/html/body/div[2]/div/div[1]/button/span'
        logout = '/html/body/div[1]/nav/ul[2]/li/a'

        driver.get("http://localhost/21/login")
        driver.maximize_window()
        time.sleep(2)

        #Login
        send_keys(driver, input_login, 'coordinador1')
        send_keys(driver, input_password, 'coordinador1')
        click_element(driver, input_submit)

        #Crear transaccion
        click_element(driver, crear_transaccion)
        send_keys(driver, input_motivo, 'Titulo de prueba selenium')
        send_keys(driver, input_cantidad, '50')
        click_element(driver, click_tipo)
        click_element(driver, seleccionar_tipo)
        click_element(driver, click_comite)
        click_element(driver, seleccionar_comite)
        click_element(driver, submit_transaccion)
        click_element(driver, input_submit_incidencia_confirm)
        click_element(driver, cerrar_aviso)
        click_element(driver, logout)





        input_login = '/html/body/div[1]/div[2]/div/form/div[2]/input'
        input_password = '/html/body/div[1]/div[2]/div/form/div[3]/input'
        input_submit = '/html/body/div[1]/div[2]/div/form/div[5]/div[2]/button'
        ordenar_transacciones = '/html/body/div/div[1]/section/div/div/div/div[2]/div/div/div/div[2]/div/table/thead/tr/th[1]'
        gestionar_transacciones = '/html/body/div[1]/aside[1]/div/nav[2]/ul/li[8]/a/p'
        denegar_transaccion = '/html/body/div[1]/div[1]/section/div/div/div/div[2]/div/div/div/div[2]/div/table/tbody/tr[2]/td[8]/a[2]'
        incidencia_con_exito = '/html/body/div[2]/div/div[2]'

        select_vista = '/html/body/div[1]/div[1]/section/div/div/div/div[2]/div/div/div/div[1]/div[2]/div/label/div/button'
        elegir_vista = '/html/body/div[1]/div[1]/section/div/div/div/div[2]/div/div/div/div[1]/div[2]/div/label/div/div/div/ul/li[4]/a/span'


        driver.get("http://localhost/21/login")
        driver.maximize_window()
        time.sleep(2)

        #Login
        send_keys(driver, input_login, 'presidente1')
        send_keys(driver, input_password, 'presidente1')
        click_element(driver, input_submit)

        #aceptar transaccion
        click_element(driver, gestionar_transacciones)
        # click_element(driver, select_vista)
        # click_element(driver, elegir_vista)
        click_element(driver, ordenar_transacciones)
        click_element(driver, denegar_transaccion)
        time.sleep(2)
        #Comprobar que no se ha creado la transaccion
        self.assertEqual(driver.find_element(By.XPATH,incidencia_con_exito).text, 'Transacción rechazada con éxito .')
        driver.quit()

    