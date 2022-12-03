import json

from random import choice

from locust import (
    HttpUser,
    SequentialTaskSet,
    TaskSet,
    task,
    between
)


HOST = "http://localhost"


class DefUsers(SequentialTaskSet):

    @property
    def on_start(self):
        with open('loadtest/users.json') as f:
            self.users = json.loads(f.read())
        self.user = choice(list(self.users.items()))    

    @task
    def login(self):
        username, pwd = self.user
        self.token = self.client.post("/21/login", {
            "username": username,
            "password": pwd,
        }).json()

    @task
    def evindencias(self):
        headers = {
            'Authorization': 'Token ' + self.token.get('token'),
            'content-type': 'application/json'
        }

        self.client.post("/evidence/create", json.dumps({
            "token": self.token.get('token'),
            "title": {
                "Evidencia"
            },
            "description" : {
                "Esto es una evidencia"
            },
            "hours" : {
                "5"
            },
        }), headers=headers)


    def on_quit(self):
        self.user = None


class Users(HttpUser):
    host = HOST
    tasks = [DefUsers]
    wait_time= between(3,5)

