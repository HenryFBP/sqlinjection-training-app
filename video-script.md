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
sudo docker-compose up -d
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

You can see that the payload is sandwiched between two single quotes.

By injecting one single quote into our username, we were able to insert SQL code into the query and modify it to our advantage.

To understand why this happens, we must look at the code.

> Open `www/login1.php` line 67.

Here, on line 67, we can see the following code:

    $username = ($_REQUEST['uid']);
    $pass = md5($_REQUEST['password']);

    $q = "SELECT * FROM users where username='" . $username . "' AND password = '" . $pass . "'";

For those of you unfamiliar with PHP syntax, the equivalent Java pseudocode is this:

    String username = request.getParameter("username");
    String pass = request.getParameter("pass");
    String query = "SELECT * FROM users where username='" + username + "' AND password = '" + md5(pass) + "'";

The injection occurs when `username` is allowed to enter a specific *context*, in this case, that context is SQL.

By *context*, I mean a space where certain symbols mean very specific things.

For example, we do not know if the symbol `'` [single quote] means "The beginning of a string literal", "The end of a string literal", or "The ASCII sequence 0x27" because the meaning of the single quote character, in any specific variant of SQL depends on characters preceding it. 

An initial thought that may cross your mind is, "The solution is simple! Just remove or encode single quotes!". This would be an example of a blocklist (a.k.a. blacklist) based approach to preventing SQL injection. This approach has failed reliably and should not be attempted.

The blocklist approach is flawed for multiple reasons. One is that mixing user input with SQL is an issue that concerns the mixing of data and code, and it can be difficult or nearly impossible to fully evaluate all of the potential ways in which a blocklist can be circumvented, primarily because of the complexity of SQL syntax. 

The best approach to preventing this is to use a SQL data binding technique called a "Prepared Statement" or a "Parameterized Query". These methods of creating SQL commands do not mix user input with SQL code, but rather, send the data separately from the code, so that the confusion of data and code is elimated.

Here is an example of the code we saw in the previous example, but fixed.

    $username = ($_REQUEST['uid']);
    $pass = md5($_REQUEST['password']);
    $stmt = $db->prepare("SELECT * FROM users where username = ? AND password = ?");
    $stmt->bind_param("s", $username); // 's' sets datatype as string
    $stmt->bind_param("s", $pass); // 's' sets datatype as string
    $stmt->execute();

If you did not know how parameterized queries work, and saw the fixed code, you may be inclined to think that the database driver simply:

- Uses string formatting with a complicated blocklist/allowlist (aka whitelist), i.e. `String.replace('?', myData)`, or
- Uses an encoding scheme that encodes single quotes and control characters

I want to reiterate: this is not how parameterized queries work. Data is not put into the SQL code itself, but sent separately from the SQL code.