# Video Script

Adapted from <https://github.com/appsecco/sqlinjection-training-app>.

I used:

- OpenShot (It is genuinely painful to use for keyframes or splicing/positioning, please use DaVinci Resolve or kdenlive instead)
- Audacity 
- GIMP
- Visual Studio Code

<https://www.youtube.com/watch?v=o7GODnWYYjE>

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

## Preamble

Hi everyone.

Before we begin, here's a bit of background about the SQL injection vulnerability.

Injection is the most common vulnerability in the OWASP Top Ten, a list of ten vulnerability categories that affect the largest number of web applications. OWASP stands for Open Web Application Security Project.

SQL injection is a vulnerability with a very high severity and impact, as it could be described as arbitrary code execution that occurs in the database. It could be leveraged to exfiltrate, modify, or delete any data within the database, given the right conditions.

As a vulnerability with a very high severity, it is a popular target for attackers and this is likely why it is number one on the OWASP Top Ten.

## Intro 

This presentation showcases some simple and more complex SQL injection attacks using a vulnerable PHP application.

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

```php

$username = $_REQUEST['uid'];
$pass = md5($_REQUEST['password']);

$q = "SELECT * FROM users where username='" . $username . "' AND password = '" . $pass . "'";

```

For those of you unfamiliar with PHP syntax, the equivalent Java pseudocode is this:

> Open `resources/java-login1-pseudocode.java`.

```java
String username = request.getParameter("username");
String pass = request.getParameter("pass");
String query = "SELECT * FROM users where username='" + username + "' AND password = '" + md5(pass) + "'";
```
The injection occurs when `username` is allowed to enter a specific *context*, in this case, that context is SQL.

By *context*, I mean a space where certain symbols mean very specific things.

For example, we do not know if the symbol `'` [single quote] means "The beginning of a string literal", "The end of a string literal", or "The character single quote, as data" because the meaning of the single quote character, in any specific variant of SQL depends on characters preceding it. 

    The beginning of a string literal:
                                     v
    SELECT * FROM table WHERE name = 'O'Reilly'

    The end of a string literal:
                                              v
    SELECT * FROM table WHERE name = 'O'Reilly'

    As data itself:
                                       v
    SELECT * FROM table WHERE name = 'O'Reilly'

You may think that removing or encoding single quotes would fully prevent SQLi. This would be an example of a blocklist (a.k.a. blacklist) based approach to preventing SQL injection. This approach has failed many times and should not be attempted. This approach is flawed for multiple reasons. 

One is that mixing user input with SQL is an issue that concerns the mixing of data and code. A blocklist does not fully separate these two different objects, but tries to sanitize the user input before it gets mixed in with data.

The other reason is that it is nearly impossible to evaluate all of the potential ways in which a blocklist can be circumvented, because of the complexity of SQL syntax. There are many different ways of creating a blocklist, and most of them can fail or have flaws. 

One additional reason is that certain types of data (such as single quotes) may get removed or transformed in unintended ways, and this can negatively affect the behavior of the application. One example is a user whose first name is "O'Reilly" having the single quote removed from their name.

The best approach to preventing this is to use a SQL data binding technique called a "Prepared Statement" or a "Parameterized Query". These methods of creating SQL commands do not mix user input with SQL code, but rather, send the data separately from the code, so that the confusion of data and code is elimated.

Here is the code we saw in the previous example, but fixed. This is also available at this link: <https://github.com/HenryFBP/sqlinjection-training-app/blob/master/www/login1-fixed.php#L70>

```php
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
```

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

```sql
SELECT * FROM users where (username='a' OR 1=1 -- ') AND (password = '0cc175b9c0f1b6a831c399e269772661')

SELECT * FROM users where (username='[a' OR 1=1 -- ]') AND (password = '0cc175b9c0f1b6a831c399e269772661')
```

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

## Attack 2.5 - XSS simulation

We saw in the previous two attacks that an attacker intentionally modifying field values could execute SQLi attacks. What if it was possible for a regular user to accidentally facilitate an SQLi attack?

With cross-site scripting, even things like cookie values could cause XSS payloads to be executed by users who are unaware of the attack.

> Navigate to <http://localhost:8000/searchproducts.php?debug=true>

In this page, we have a few places user input is used.

One place is the search box, and the other is the drop-down box.

Let's put `a` in the search box and see what happens.

> Put `a` in the search box. Click "Search!"

Looks like the SQL is built without parameterization, so a payload like this:

> Paste the payload into the search box.

    ' or 1=1;-- //

Should be able to cause SQLi.

> Click "Search!"

I'm not going to go into exploiting this specific box further, but demonstrate what XSS could look like in an application.

This button, "Simulate XSS in drop-down", will inject a value into every list item to simulate how an XSS attack could facilitate an SQL injection attack.

For example, if we put `a` into the input field,

> Put `a` into the XSS input. Click "Simulate XSS".

> Inspect Element (CTRL-SHIFT-C) on the drop-down.

We can see that every value tag of the `<option>` elements gets changed to `a`.

When we go to search,

> Click "Search! (injected)

Our query returns things starting with "A".

We can put the same payload into this, to simulate what effect XSS would have on our application. I'm not going to do this, but you get what effect a successful XSS attack could have on the DOM.

I'm going to now move onto SQL injection facilitated by cookies.

At the top, you can see a "welcome banner" in light pink.

This "welcome banner" comes from a cookie, which I will show you.

> Open the cookies view and show the cookie.

In fact, I can change this value to show a different message.

> Change the cookie `welcome-banner`'s value to `hello world!`. Refresh page.

We can see the value is different now.

If we were to change this cookie value to a payload that modifies the DOM, then we could cause XSS to take place, which would enable SQL injection.

I'm going to show you the payload and explain what happens to the DOM before I execute the attack, as the payload may look complicated.

> Show the below payload. Talk about it freely.

Points
- Closing the p tag
- Adding a script tag
- Changing the option element's value to an SQLi payload


```html
I'm a payload!</p><script>$('option')[0].value="' OR 1=1; -- //"; alert("success!");</script><p>
```

Now, I'm going to set the cookie value to this. In a real setting, an attacker may try to set a user's cookie value through a number of means, but XSS is the primary way.

The payload I'm using is percent-encoded so that no invalid cookie characters like spaces, semicolons, etc; get used.

```
I'm%20a%20payload!</p><script>$('option')[0].value=%22'%20OR%201=1%3B%20--%20//%22%3B%20alert(%22success!%22)%3B</script><p>
````

> Change the cookie `welcome-banner`'s value to the percent-encoded cookie. Refresh page.

You can see the alert popped up, so our code executed.

> Inspect element on the 1st `<option>` element.

And we can also see that our payload is in this `<option>` element. It's impossible to tell, from a user's perspective, that anything has gone wrong. Let's select this first option and use it to search.

> Select the first option and search with it.

And our SQLi payload has been executed.

## Attack 3 - Verbose SQL Error based Injection

This last attack details what could happen if your application is configured to display error messages to your users.

The previous attacks allowed us to log ourselves in, and select extra rows, but that's about it. What if we actually wanted to retrieve data from the database?

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

You can use blocklists/allowlists, but there is always the chance that you've missed something, and that one well-crafted input could slip through. Many SQL variants allow for a diverse range of encoding and escaping methods, and a failure to consider any of those could result in your blocklist/allowlist falling short of preventing SQL injection.

In certain scenarios, parameterized queries actually cannot be used. In this situation, you may want to use allowlists, or encoding, and avoid blocklists unless absolutely necessary.

Shown on screen is a link from OWASP that covers SQL injection prevention.

<https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html>

All of the resources shown in the video are available online, including this video's script, and some image resources, at the link shown on screen.

<https://github.com/HenryFBP/sqlinjection-training-app/>

I want to say thanks to the original repository creators, AppSecCo, for putting this source code on GitHub. Their GitHub page is on-screen as well.

<https://github.com/appsecco/>