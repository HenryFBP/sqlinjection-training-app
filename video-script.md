Adapted from <https://github.com/appsecco/sqlinjection-training-app>.

TODO: YOUTUBE LINK GOES HERE.

Script for a quick video demonstrating SQL injection and how it is enabled.

## Prerequisites

-   docker-compose
-   docker

```
git clone https://github.com/HenryFBP/sqlinjection-training-app
cd sqlinjection-training-app
sudo docker-compose build
sudo docker-compose start
sudo docker-compose ps
curl http://localhost:8000/resetdb.php
curl http://localhost:8000
```

> Get the prerequisites ready. 

> You can access the web app at <http://localhost:8000> in a browser.

> Go to it and make a few users. The database starts with no users initially.

## Intro

Hi everyone. This video showcases some simple and more complex SQL injection attacks using a vulnerable PHP application.

We'll first execute an attack, and then dissect why the attack was successful by analyzing the source code.

> Navigate to <http://localhost:8000/login1.php?debug=true>.

## Attack 1: login1.php

Here, we have a simple login page. Let's try putting in 'a' for the username and 'b' for the password.

> Put in `a` for username, `b` for password. Submit.

You can see that the resulting query looks like a pretty simple SQL statement.

But what if we try to escape the closing single quote after 'a'?

> Put in [`a' OR 1=1 -- `] for a username. The ending space is important.

> Highlight the payload in the query.

We're logged in! But we didn't give a valid password.

You see that the payload is sandwiched between two single quotes.

By injecting one single quote into our username, we were able to insert SQL code into the query and modify it to our advantage.

To understand why this happens, we must look at the code.

> Open `login1.php` line 70.

TODO