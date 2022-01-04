from typing_extensions import Self
import unittest
from selenium import webdriver
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.keys import Keys



class suite(unittest.TestCase):
    
    def pruebaForm(self):
        options = webdriver.ChromeOptions()
        driver = webdriver.Chrome(options=options)
        driver.get("http://localhost/21/login")
        username = driver.find_element_by_name('username')
        password = driver.find_element_by_name('password')
        login = driver.find_element_by_class_name('btn btn-primary btn-block')
        username.sendKeys('coordinador1')
        password.sendKeys('coordinador1')
        login.click()
        actualUrl="http://localhost/21"
        expectedUrl= driver.getCurrentUrl()
        self.assertEquals(expectedUrl,actualUrl)
        
        





