Adapted from <https://github.com/appsecco/sqlinjection-training-app>.

TODO: YOUTUBE LINK GOES HERE.

Script for a quick video demonstrating SQL injection and how it is enabled.

## Prerequisites

-   docker-compose
-   docker

```
git clone https://github.com/HenryFBP/sqlinjection-training-app
cd sqlinjection-training-app && sudo docker-compose build && sudo docker-compose start
sudo docker-compose ps
curl http://localhost:8000
```

> Get the prerequisites ready. 

> You can access the web app at <http://localhost:8000> in a browser.

## Intro

Hi everyone. This video showcases some simple and more complex SQL injection attacks using a vulnerable PHP application.

We'll first execute an attack, and then dissect why the attack was successful by analyzing the source code.

## Attack 1: login1.php

TODO