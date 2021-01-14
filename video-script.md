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

    $username = $_REQUEST['uid'];
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

Here is the code we saw in the previous example, but fixed. This is also available at this link: <https://github.com/HenryFBP/sqlinjection-training-app/blob/master/www/login1-fixed.php#L70>

    $username = $_REQUEST['uid'];
    $pass = md5($_REQUEST['password']);
    $stmt = $db->prepare("SELECT username, fname FROM users where username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $pass); // 's' sets datatype as string, 2 's' for 2 parameters
    $result = $stmt->execute(); // returns # of how many rows

    if($result > 0) {
        $stmt->bind_result($ret_username, $ret_fname);
        $stmt->fetch();
        echo "DEBUG: Returned '$ret_username', '$ret_fname'";
    } else {
        echo "DEBUG: No uname+pass matched."
    }

You can see the string concatenation is gone, and rather, data is not mixed with the SQL code, but put into a construct separately.

If you did not know how parameterized queries work, and saw the fixed code, you may be inclined to think that the database driver simply:

1.  Uses string formatting with a complicated blocklist/allowlist (aka whitelist), i.e. `String.replace('?', myData)`, or
2.  Uses an encoding scheme that encodes single quotes and other SQL control characters, i.e. parentheses and double quotes


This is not the case. I want to reiterate: This is not how parameterized queries work. Data is not put into the SQL code, but sent separately from the SQL code.

## Attack 2: TODO

Now that we have a basic understanding of 