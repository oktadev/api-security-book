# Sanitizing Data {#sanitizing}

<div class="chapter-author">By Brian Demers</div>

The inputs to your application represent the most significant surface area of attack for any application. Does your API power forms for user input? Do you display data that didn’t originate in your API? Do users upload files through your API?

Any time data crosses a trust boundary - the boundary between any two systems - it should be validated and handled with care. For example, a trust boundary would be any input from an HTTP request, data returned from a database, or calls to remote APIs.

Let’s start with a simple example: a user submission to the popular internet forum, Reddit.  A user could try to include a malicious string in a comment such as:

```html
<img src onerror='alert("haxor")'>
```

If this were rendered as is, in an HTML page, it would pop up an annoying message to the user.  However, to get around this, when Reddit displays the text to the user, it is escaped:

```html
&lt;img src onerror=&#39;alert(&quot;haxor&quot;)&#39;&gt;
```

which will make the comment appear as visible text instead of HTML, as shown in <a href="#fig_sanitizing_reddit" class="figref"></a>.

<figure id="fig_sanitizing_reddit">
  <img src="__DIR__/images/reddit.png" alt=""/>
  <figcaption>Reddit properly escapes user input</figcaption>
</figure>

In this example the trust boundary is obvious as any user input should not be trusted.

There are a few different approaches you can use when validating input:

* Accept known good
* Reject bad
* Sanitize
* Do nothing


## Accept Known Good {#sanitizing-accept-good}

The known good strategy is often the easiest and most foolproof of the given options. With this approach each input is validated against an expected type and format:

* Data type, (Integers are Integers, booleans are booleans, etc)
* Numeric values fall within an expected range (for example: a person’s age is always greater than 0 and less than 150)
* Field length is checked
* Specially formatted string fields such as zipcode, phone number, and social security number are valid

Most web frameworks have some type of declarative support to validate input fields built in. For example,  in the Node.js world you can use the popular `validator` package to validate different types of input:

```js
import validator from 'validator';
validator.isEmail('foobar@example.com');
```

## Reject Bad Inputs {#sanitizing-reject-bad}

Rejecting known invalid inputs is more complicated than only accepting known good inputs (which we talked about above) and far less accurate.  This strategy is typically implemented as a blacklist of strings or patterns.  This technique may require many regular expressions to be run against a single field which may also affect the speed of your application. It also means that this blacklist will require updates any time a new pattern needs to be blocked.

Take a typical use-case of blocking ‘bad-words’.  This problem is incredibly complex as language usage varies across locale. These complexities can be demonstrated using the simple word: `ass`. It would be pretty easy to block this word alone, but doing so would also block the proper use of the word referring to donkeys. Then you need to think about both variations of the word and where those letters happen to come together: ‘badass,’ ‘hard-ass,’ ‘amass,’ ‘bagasse’, the first two are questionable while the second two are fine. Even if you block all of these and the thousands of other words that contain these three letters, there are still variations that would make it past: ‘4ss’, ‘as.s,’ ‘azz,’ ‘@ss,’ ’āss,’ or ‘\41\73\73’ (escaped characters). As time goes on the list of blocked words would increase raising the total cost of the solution.

Another famous example of this technique is antivirus software. Your antivirus updates every few days to get a new blacklist of items to scan. And we all know how well that works ;)


## Sanitize Inputs {#sanitizing-inputs}

Sanitizing inputs can be a good option when the input format is not strict but still somewhat predictable, such as phone numbers or other free-text fields. There are a few different ways to sanitize inputs, you could use a whitelist, a blacklist, or escape input.

### Sanitize Input Using a Whitelist
When sanitizing data with a whitelist, only valid characters/strings matching a given pattern are kept.  For example, when validating a phone number there are multiple formats people use, US phone numbers could be written as `555-123-1245`, `(555) 123-1245`, `555.123.1245`, or a similar combination. Running any of these through a whitelist that only allows numeric characters would leave `5551231245`.

### Sanitize Input Using a Blacklist
A blacklist, of course, is the exact opposite of a whitelist. A blacklist can be used to strip HTML `<script>` tags or other non-conforming text from inputs before using input values.  This technique suffers from the same shortcomings of the above section, <a href="#sanitizing-reject-bad" class="section">Rejecting Bad Inputs</a>. This type of sanitization must be done recursively until the value no longer changes. For example if the value <code>&lt;scr<b>&lt;script</b>ipt foo bar</code> is only processed once the result would be still contain `<script`, but if done recursively, the result would be `foo bar`.

### Sanitize Input Using Escaping
Escaping input is one of the easiest and best ways to deal with free-form text.  Essentially, instead of trying to determine the parts of the input that are safe (as with the above strategies), you assume the input is unsafe. There are a few different ways to encode strings depending on how the value is used:

#### HTML/XML Encoding
Example Input:<br>
`<img src onerror='alert("haxor")'>`

Result:<br>
`&lt;img src onerror=&#39;alert(&quot;haxor&quot;)&#39;&gt;`

#### HTML/XML Attribute Encoding
Example Input:<br>
`<div attr="" injectedAttr="a value here"><div attr="">`

Result:<br>
`<div attr="&quot;&nbsp;injectedAttr&#61;&quot;a&nbsp;value
&nbsp;here&quot;&gt;&lt;div attr=&quot;">`

#### JSON Encoding
Example Input:<br>
`{"key": "", anotherKey": "anotherValue"}`

Result:<br>
`{"key": "\", anotherKey\": \"anotherValue\""}`

#### Base64 Encoding
Example Input:<br>`any random string or binary data`

Result:<br>
`YW55IHJhbmRvbSBzdHJpbmcgb3IgYmluYXJ5IGRhdGE=`

There are ways to escape just about any format you need SQL, CSV, LDAP, etc.

### Do Nothing
The last type of input validation is the no-op. Along with being the easiest to implement it is the most dangerous and most strongly discouraged! Almost every application takes input from an untrusted source. Not validating inputs puts your application and users at risk.

## Common Attacks {#sanitizing-common-attacks}

The examples in this chapter have discussed ways to validate inputs but have only hinted at the type of attacks used when inputs are not properly sanitized. Let’s look at those potential attacks, and how to prevent them, now.

### SQL Injection Attacks
SQL injection is by far the most common form of data sanitization attack, and remains number one in the [OWASP Top 10](https://www.owasp.org/images/7/72/OWASP_Top_10-2017_%28en%29.pdf.pdf) (a popular list of the most commonly found and exploited software vulnerabilities). It's held the number one spot for over 10 years now.

SQL injection occurs when an attacker is able to query or modify a database due to poor input sanitization. Other query injection attacks are similar, as most are typically a result of string concatenation.  In the following example, a simple user query string is built with concatenation.

```sql
userId = getFromInput("userId");
sql = "SELECT * FROM Users WHERE UserId = " + userId;
```

If the `userId` were `jcoder` the SQL query would be `"SELECT * FROM Users WHERE UserId = jcoder`, however, a malicious attacker might input `jcoder; DROP TABLE ImportantStuff` which would result in two statements being executed:

```sql
SELECT * FROM Users WHERE UserId = jcoder;
DROP TABLE ImportantStuff
```

Similarly, the user could enter `jcoder OR 1=1` which would query for a user with the ID of `jcoder` OR `true` (`1=1` is always true), this would return all of the users in the system.

The cause of this issue is the use of poor string concatenation.  In the example above, the value of the `userId` input crosses a trust boundary and ends up getting executed. The best way around this is to use SQL prepared statements.  The syntax for using prepared statements varies from language to language but the gist is that the above query would become `SELECT * FROM Users WHERE UserId = ?`.  The question mark would be replaced with the input value and be evaluated as a string instead of changing the query itself.

Most web frameworks and ORM libraries provide tools to protect against SQL injection attacks, be sure to look through your developer library documentation to ensure you’re using these tools properly.

### XSS - Cross Site Scripting
A cross-site scripting attack (XSS) is an attack that executes code in a web page viewed by a user. There are three different types of XSS attacks:

* **Stored XSS** - A persisted (in a database, log, etc) payload is rendered to an HTML page. For example, content on an forum.
* **Reflected XSS** - Attack payload is submitted by a user, the rendered server response contains the executed code. This differs from Stored XSS where as the attack payload is not persisted, but instead delivered as part of the request, eg. a link: `http://example.com/login?userId=<script>alert(document.cookie)</script>`
* **DOM based XSS** - The attack payload is executed as the result of an HTML page’s DOM changing. With DOM based XSS the attack payload may not leave the victim’s browser. The client side Javascript is exploited.

There are [tons of resources online](https://www.owasp.org/index.php/Cross-site_Scripting_%28XSS%29) that cover this topic in great detail, so I’ll only provide a basic example here. Earlier in this chapter the string `<img src onerror='alert("haxor")'>` was posted as a Reddit comment. If this string isn’t correctly escaped it would have resulted in an annoying popup, shown in <a href="#fig_sanitizing_alert" class="figref"></a>.

<figure id="fig_sanitizing_alert">
  <img src="__DIR__/images/alert.png" alt="">
  <figcaption>A JavaScript alert popup</figcaption>
</figure>

You may see `alert()` used throughout examples when describing these attacks. The idea is if you can cause an alert to happen in the browser, you can execute other code that does something more malicious like sends your information (cookie, session ID, or other personal info) to a remote site.

### File upload attacks
It is common for sites to support file uploads, particularly images such as profile avatars or photos. When uploading files, it is necessary to validate the type, size, and contents of these files.  For example, if a user is uploading an avatar image, it’s important to ensure the newly uploaded file is actually an image.

If an attacker can upload a PHP file named `avatar.php` instead of an image file, then later retrieve the file, unexpected and disastrous behavior may occur. Imagine what would happen if that file is executed on the server, you could have a remote code exploit on your hands. There are a few things you can do to prevent this type of attack:

Validate expected file types
Check that file size is reasonable (if someone is uploading a 1GB image, you might have a problem)
If storing the file to disk, do NOT use a user input field as part of the file name, eg: `../../../etc/config.file`
Always serve the files with the correct Content-Type header (image/png, audio/mpeg)
Run a virus scan on all uploaded files
Do not allow uploads of web executed files: php, cgi, js, swf, etc.
Process the files - rename, resize, remove exif data, etc - before displaying back to the user

## Look For Other Attack Vectors {#sanitizing-attack-vectors}

Inputs are everywhere, often only evident in hindsight. User input and file uploads are just the tip of the iceberg, but what if we consider more than input and instead the code itself? Here are a couple of examples to illustrate this point.

### Your Dependencies
Do you trust all of your dependencies? How about all of the transitive dependencies of your application? It is not uncommon to for an application to have a page that lists its dependencies versions and licenses (the later might even be required depending on the license). The popular Node package manager (npm) has had a few projects which have contained [maliciously formed license fields](https://blog.npmjs.org/post/80277229932/newly-paranoid-maintainers). In another npm incident, [packages ran malicious scripts](https://iamakulov.com/notes/npm-malicious-packages/) upon installation automatically that uploaded the user’s environment variables to a third party.

Every dependency is code you include from other systems across your trust boundary. Properly inspecting and validating your dependencies is a critical first step of any input sanitation plan. GitHub recently introduced [automated security alerting](https://blog.github.com/2017-11-16-introducing-security-alerts-on-github/) to let you know when your dependencies might have security issues. Pay attention to these and you can prevent a lot of headaches.

### Inbound HTML Requests
Almost all values from an HTTP request can be changed by the sender and need to be handled accordingly. To help illustrate this, here is a simple HTTP POST including numerous headers to `http://example.com/submit-me`:

```http
POST /submit-me HTTP/1.1
Host: example.com
Accept: */*
Referer: http://example.com/fake.html
Accept-Language: en-us
Content-Type: application/x-www-form-urlencoded
Accept-Encoding: gzip, deflate
User-Agent: My Fake UserAgent <img src onerror='alert("haxor")'>
Content-Length: 37
Connection: Keep-Alive
Cache-Control: no-cache

foo=bar&key=value
```

You can see right away: request headers are user input too.  Imagine for a moment that an HTTP client maliciously changes the User-Agent header. The logged User-Agent may falsely identify a request as coming from a different client application than the one in which it really originated.originated from. While that’s unlikely to affect the current request, it might cause confusion in the application’s logging and reporting system.

Further, the User-Agent could be visible from an internal web application that doesn’t sanitize the User-Agent values before displaying them. In this case, an HTTP client could maliciously modify their User-Agent to any JavaScript code they want which would then be executed in an internal user’s browser via XSS.

As these examples illustrate, even sanitizing relatively innocuous inputs is an important part of an overall security strategy.

## Best Practices for Secure Data {#sanitizing-best-practices}

While this chapter provides an overview of a few common types of attacks, there are many more out there.

First, you don’t need to be an expert to prevent these attacks, but you do need to have some knowledge of them. The Open Web Application Security Project at <a href="https://owasp.org" class="url">OWASP.org</a> is a great source information and examples on how to secure your application, often in multiple programming languages.

One of the most straightforward means of prevention is not to reinvent the wheel, and use an existing framework. Most frameworks contain tools to properly escape values, both on the frontend and backend, when used correctly.

Next, don’t forget to monitor your application dependencies. There are mailing lists as well as open source and commercial tools to help you. New CVEs (Common Vulnerabilities and Exposures) are reported all of the time. For example, at the time of this writing a popular Java Web Container, Apache Tomcat 8, has about [60 CVEs](https://tomcat.apache.org/security-8.html) reported (and fixed). These reports, and the subsequent releases indicate that the project takes security seriously and updates regularly.

And finally, trust no one! As you have seen, any input into your API is an attack vector. Everything from an HTTP request to data returned from a database query to the files user upload could be dangerous. Proper data validation and sanitization goes a long way to help mitigate risk.

