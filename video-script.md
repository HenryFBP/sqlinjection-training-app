# Video Script

Adapted from <https://github.com/appsecco/sqlinjection-training-app>.

TODO: YOUTUBE LINK GOES HERE.

Script for a quick video demonstrating SQL injection and how it is enabled.

## Format

Normal text is to be spoken.

> Quoted/indented text, like this, represents actions the presenter should execute.

## Prerequisites

-   docker-compose
-   docker

    git clone https://github.com/HenryFBP/sqlinjection-training-app
    cd sqlinjection-training-app
    sudo docker-compose build
    sudo docker-compose up -d
    sudo docker-compose ps
    curl http://localhost:8000/resetdb.php
    curl http://localhost:8000
    xdg-open http://localhost:8000

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

It looks like our username is inserted directly in-between two single quote characters. What if we try to modify the query after our username?

> Put in [ `a' OR 1=1 -- ` ] for a username. The ending space is important.

> Put in `b` for password. Submit.

> Highlight the payload in the query, 

    SELECT * FROM users where username='a' OR 1=1 -- ' AND password = '098f6bcd4621d373cade4e832627b4f6'

    SELECT * FROM users where username='[a' OR 1=1 -- ]' AND password = '098f6bcd4621d373cade4e832627b4f6'


Our attack was successful. We're logged in, even though we didn't give a valid password.

You can see that the payload is sandwiched between two single quotes.

By injecting one single quote into our username, we were able to insert SQL code into the query and modify it to our advantage.

More specifically, we get logged in, because our attack causes the ResultSet obtained from the query to contain all rows in the database. The "AND" clause is removed by the double-hypen, and replaced with an "OR" clause that always evaluates to true, `1=1`.

To understand why this happens, we must look at the code.

> Open `www/login1.php` line 67.

Here, on line 67, we can see the following code:

    $username = $_REQUEST['uid'];
    $pass = md5($_REQUEST['password']);

    $q = "SELECT * FROM users where username='" . $username . "' AND password = '" . $pass . "'";

For those of you unfamiliar with PHP syntax, the equivalent Java pseudocode is this:

> Open `resources/java-login1-pseudocode.java`.

    String username = request.getParameter("username");
    String pass = request.getParameter("pass");
    String query = "SELECT * FROM users where username='" + username + "' AND password = '" + md5(pass) + "'";

The injection occurs when `username` is allowed to enter a specific *context*, in this case, that context is SQL.

By *context*, I mean a space where certain symbols mean very specific things.

For example, we do not know if the symbol `'` [single quote] means "The beginning of a string literal", "The end of a string literal", or "The character single quote, as data" because the meaning of the single quote character, in any specific variant of SQL depends on characters preceding it. 

    The beginning of a string literal:
                                     v
    SELECT * FROM table WHERE name = 'data'

    The end of a string literal:
                                          v
    SELECT * FROM table WHERE name = 'data'

    As data itself:
                                           v
    SELECT * FROM table WHERE name = 'jack\'s back'

An initial thought that may cross your mind is, "The solution is simple! Just remove or encode single quotes!". This would be an example of a blocklist (a.k.a. blacklist) based approach to preventing SQL injection. This approach has failed reliably and should not be attempted.

The blocklist approach is flawed for multiple reasons. One is that mixing user input with SQL is an issue that concerns the mixing of data and code, and it is nearly impossible to evaluate all of the potential ways in which a blocklist can be circumvented, because of the complexity of SQL syntax. 

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

If you did not know how parameterized queries work, and saw the fixed code, you may be inclined to think that the database driver either:

1.  Uses string formatting with a complicated blocklist/allowlist (aka whitelist), i.e. `String.replace('?', myData)`, or
2.  Uses an encoding scheme that encodes single quotes and other SQL control characters, i.e. parentheses and double quotes

But this is not the case. 

To summarize, with parameterized queries, user data is not put into the SQL code. Blocklists, allowlists, or encoding are not used, but the data is sent separately from the SQL code.

## Attack 2 - Slightly different: login2.php

Now that we have a basic understanding of SQL injection, let's look at a slightly modified query that defends against our original attack, but offers no defense other than that.

This is another very simple SQL injection attack and can show you how SQLi attacks are built. 

> Navigate to <http://localhost:8000/login2.php?debug=true>.

This login page looks identical, so let's just try our original payload.

> Put in [ `a' OR 1=1 -- ` ] for a username. The ending space is important.

> Put in `b` for a password. Submit.

Looks like it failed. We get a syntax error instead of a successful injection.

> Highlight the payload in the query.

    SELECT * FROM users where (username='a' OR 1=1 -- ') AND (password = '0cc175b9c0f1b6a831c399e269772661')

    SELECT * FROM users where (username='[a' OR 1=1 -- ]') AND (password = '0cc175b9c0f1b6a831c399e269772661')

This fails because, even though we have a comment, dash-dash (`--`), injected, there is an opening paren that does not find its closing paren.

> Note: 'paren' is the singular form of 'parentheses', a/k/a 'bracket'.

The closing paren is AFTER the comment, (dash-dash), and gets ignored by the SQL parser.

However, it is extremely easy to beat this new query. We only have to add a closing paren directly before the comment begins.

> Update the payload to the new version: 

    New:   a' OR 1=1) -- 
    Old:   a' OR 1=1  --  

> Note an ending space is required after the comment.

> Submit again and enjoy being logged in.

As in the previous example, the best way to fix this is not to ban parentheses or single quote characters, but to use parameterized queries.

## Attack 3 - Verbose SQL Error based Injection

This last attack details what could happen if your application is configured to display error messages to your users.

The previous 2 attacks allowed us to log ourselves in, but that's about it. What if we actually wanted to retrieve data from the database?

Suppose you're an attacker

> Navigate to <http://localhost:8000/login1.php>. Not in debug mode.

This payload:

    ' or 1 in (select password from users where username = 'admin') -- //

Which results in the server running this SQL code:

    SELECT * FROM users WHERE username='' or 1 in (select password from users where username = 'admin') -- //' AND password = '0cc175b9c0f1b6a831c399e269772661'

Selects a password hash, which is a String type, from the database, and attempts to compare it to an integer. This produces an error, and that error message contains the data being compared. If your application displays errors to users, this could allow the password hash to be shown to an attacker.

So, let's run it and see what happens.

> Put in [ `' or 1 in (select password from users where username = 'admin') -- //` ] for the username and `b` for the password. Submit.

Looks like a password hash is echoed back to the user! As an attacker, we could now try to crack this and get the password back.

The defense against this specific vulnerability could be one of two defenses:

1. Using parameterized queries would prevent this from occurring in the first place, but
2. Not showing verbose error messages would have also prevented this specific vulnerability.

## Summary

In summary, the only reliable way to defend against SQL injection is to use parameterized queries or prepared statements.

You can use blocklists/allowlists, but there is always the chance that you've missed something, and that one well-crafted input could slip through.

All of these resources are available online, including this video's script, at the link shown on screen.

<https://github.com/HenryFBP/sqlinjection-training-app/>

I want to say thanks to the original repository creators, AppSecCo, for putting this source code on GitHub. Their GitHub page is on-screen as well.

<https://github.com/appsecco/>