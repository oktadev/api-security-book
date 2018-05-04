# Managing API Credentials {#api-keys}

<div class="chapter-author">By Joël Franusic</div>

A critical part of designing an API is determining how to grant users access to sensitive or important parts of it. While many APIs have publicly accessible endpoints that don’t require authentication, the vast majority of APIs require a user to authenticate before any request can be fulfilled.

You need to authenticate with Stripe to charge a credit card, you have to authenticate with Twilio to send an SMS, and you have to authenticate with SendGrid to send an email. There’s really no way to avoid authentication.

This chapter will cover two main aspects of managing API tokens:

* Protecting tokens that you use to connect to other APIs
* Advice and suggestions for what sort of API token to use for an API that you are building

## Keep Your Credentials Private {#api-keys-private}

No matter whether you are using an API or building your own, the advice applies to you: Never put an API secret into your code. Never ever!

The biggest problem with storing credentials in source code is that once a credential is in source code it’s exposed to any developers who have access to that code, including your version control provider.

Many people use GitHub to host their source code — what happens when you push company code containing sensitive credentials? That means GitHub staff can now view your credentials; it’s a security risk. If that project is later shared with contractors, partners, customers, or even made public, your secret is no longer secret. The same is true for open source projects — accidentally publishing test API tokens or other sensitive data will cause enormous problems. There are attackers who scan GitHub commits looking for sensitive data like Amazon Web Services API tokens and then use these credentials to do things like mine cryptocurrencies, form botnets, and enable fraud.

If you can’t store your credentials in source code, how should you authenticate to your databases or 3rd party APIs? The short answer is to use environment variables instead. What that means is ignoring what you see in sample code (where secrets are entered directly into source) and instead loading secrets from environment variables which are managed from outside your source code and will not be stored in version control and every team member’s text editor.

Below are two short snippets of sample code that demonstrate how important it is to use environment variables to store credentials.

Below we have some example Python code from Twilio. In this example, the `AC01a2bcd3e4fg567h89012i34jklmnop5` string is the "username" or "account ID" and the the `01234567890a12b34c567890de123fg4` string is the API token.

    from twilio.rest import Client

    account_sid = "AC01a2bcd3e4fg567h89012i34jklmnop5"
    auth_token = "01234567890a12b34c567890de123fg4"
    client = Client(account_sid, auth_token)

As you can see above, these API credentials are hard-coded into the program’s source. This is a big no-no. Instead, as an example of what you *should* do, take a look at this example code from SendGrid, which uses the "os.environ.get" method in Python to grab the SendGrid API token from the `SENDGRID_API_KEY` environment variable.

    import os
    import sendgrid
    from sendgrid.helpers.mail import *

    apikey=os.environ.get('SENDGRID_API_KEY')
    sg = sendgrid.SendGridAPIClient(apikey)

By pulling sensitive credentials from environment variables, which can be managed by configuration management and secret management software, your application code will remain credential-free.

Using environment variables to store secrets is an incredibly important first step to take in securing your code.

## "What Kind of API Token Should I Use?" {#api-keys-what-kind}

Now that we’ve covered general advice for storing and using API tokens, let’s review common options for securing your API. We’ll cover the different types of API tokens, touch on the advantages and disadvantages of each, and summarize with suggestions and a recommended approach.

### Secure Your API

First, let’s dig into some best practices:

* If your API is intended to be used to support an end user application, secure it using OAuth 2.0 to act as a Resource Server (RS) as defined by the OAuth 2.0 specification.
* If your API is intended to be used as a service by other software, secure your API using an “API token” - a string that is unique for each client.

The first approach, implementing an OAuth 2.0 Resource Server, is intended to be used when an application is acting on behalf of the person using the app. The Instagram and Spotify APIs are great examples where every action of the API needs the user’s context to make sense.

The second approach, using an API token, is the best approach for automated software. Stripe, Twilio, and SendGrid are examples of this type of an API. While you certainly can, and eventually should consider, implementing OAuth 2.0 access tokens, doing so may be more overhead than telling your users to just use an API token.

Regardless of approach, the following patterns apply:

* Use the `Authorization: Bearer ` header for authenticating to your API. The single most important reason is that URL parameters are captured in server access logs and caches. Any “secret” included as a parameter is no longer a secret. As a standard defined by RFC 6750, most HTTP clients have built-in methods for using Bearer tokens.
* Further, instead of using a simple unique string, use a JSON Web Token or JWT as the Bearer token. By using JWT and the claims defined by RFC 7519, you can support a wide range of scenarios using just one authentication method. And, since JWT is such a widely adopted standard, most programming languages and frameworks have first-class JWT decoding and validation built in. You get more flexibility and wider compatibility with less work!

Another important thing to keep in mind is that by using a JWT as a Bearer token, you can support both token types that we describe above. A JWT works equally well as an OAuth 2.0 access token or as an “opaque” generic API token. You could start by just offering authenticating with an API token, but later add support for OAuth 2.0 and give your customers the features that OAuth 2.0 easily enables like automatic rotation of access tokens. You and your users get a clear, simple upgrade path for the life of your API.

## Other Options for Authentication to Your API Service {#api-keys-other-options}
Now, while I suggest that you have your clients authenticate to your API using JWTs as Bearer tokens, there are other patterns you will encounter. Below is a short list of other approaches to API authentication. Keep in mind that this is by no means an exhaustive list, just a list of the most common approaches and their tradeoffs:

### Basic Authentication
This is the good ‘ole “username and password” form of authentication method. Some APIs will use other words for “username” and “password” for example, Twilio calls the “username” the “Account SID” and the “password” the “Auth Token” but it works exactly the same.

If you decide to use Basic Auth to secure your API, keep in mind that your “username” and “password” should be random strings and not the same as the account username and password. You can generate these values by using an entropy source from your operating system (`/dev/random` on Unix-like systems or CryptGenRandom on Windows systems)

### Opaque Tokens
These were the most common type of API token for APIs designed before OAuth 2.0 was standardized. Companies that use these types of token to secure their APIs include Stripe (with tokens that look like this: `sk_test_ABcdefGHiJkL0MnOpQ1rstU2`), and Okta (with tokens that look like this `00aBCdE0FGHijklmNO1pQ2RStuvWx34Y5z67ABCDEf`). These tokens have no relationship with the account information and mean absolutely nothing outside those systems.

As with basic auth strings, we suggest that you generate opaque tokens using an entropy source from your operating system.

Another best practice for opaque tokens is to allow multiple tokens to be issued and used at the same time, as this will allow for key rotation.

### Signed or Hashed Tokens
These tokens are cryptographically signed or hashed and can either be opaque tokens or contain carry information about themselves. One reason to use a signed or hashed token is to allow your API to validate tokens without the need for a database lookup. The best supported token type in this category is the JWT used for OpenID Connect. Other examples of tokens in this category include PASETO and Hawk. In general, we suggest using JWTs as described above.

## Advanced API Token Considerations {#api-keys-advanced}
Using OAuth 2.0 with OIDC, or just a JWT as a Bearer token is a significant milestone in the ongoing task of keeping your API secure. Depending on use case, type of data, and type of operations your API provides, you may need to consider additional steps to secure your API. Keep in mind that these extra considerations might not be appropriate for every kind of API. If your API is a fan-made API that gives programmatic access to Star Wars data (like swapi.co), simple API keys are probably sufficient. However, if your API deals with things in the real world or any form of sensitive data, you should consider the options below and choose the combination appropriate for your API and fits your compliance requirements.

### Implement API Token Rotation
A great next step to take in securing an API is to rotate the API token automatically. Like passwords, regularly changing an API token will limit the damage a leaked or misplaced API token can cause. More importantly, by considering and implementing this from the beginning, if a token is leaked or when an employee leaves the team, you have a process for quickly responding and protecting your systems.

Additionally, one of the great side-effects of frequent API token rotation is that it forces best security practices. Sometimes, when a team is in a rush to deliver a critical feature, corners get cut and hard coding an API token instead of storing it properly may save a few minutes in the short term. If you rotate tokens on a regular basis, developers have to follow the rules, otherwise their code will stop working on the next rotation.

If you are using OAuth 2.0 to secure your API, token rotation is built-in to the OAuth 2.0 standard: An “access_token” always has a limited lifespan and must be rotated periodically using the “refresh_token”. As an additional benefit, if you’re using an OAuth server such as Okta, when you exchange the refresh_token for a new access_token, your authorization policies are re-evaluated. If a user’s API access has been limited, increased, or even revoked, your application will know.

Outside of OAuth 2.0, there isn’t an accepted best practice for implementing token rotation. Therefore your best and easiest option is to implement OAuth 2.0. Once you have a system in place to manage your API tokens, it makes sense to start rotating API tokens on a regular basis. Your specific rotation schedule will depend on the use case. For read/write operations in banking or healthcare, rotating every 5 or 10 minutes might be necessary. For read only access to a public Twitter feed, annually is probably sufficient.  Regardless, you should always rotate keys after an employee leaves the team to protect against accidental or intentional misuse of API tokens by former employees.

Ideally, key rotation should also be paired with configuring your API to log events into a "Security and Information and Event Management” (SIEM) system that you can use to monitor your API for suspicious events.

### Monitor for Token Leaks
In addition to the use of SIEM systems as suggested above, an advanced technique is to scan sites like GitHub and S3 for leaked API keys. No best practices have emerged in this area yet, but a good technique should include automatically disabling and notifying end users when a token has been discovered in public as part of a scan.

Quite a few open source projects can be found that will scan for leaked tokens, a good way to find these services is to search for “github credential scan”

### Bind Tokens to TLS Sessions
Finally, an interesting emerging technique that I’m keeping my eye on is the binding of tokens to TLS sessions. This technique is described in [RFC 5056](https://tools.ietf.org/html/rfc5056) and [RFC 5929](https://tools.ietf.org/html/rfc5929).

The basic idea with “channel binding” is to tie an API token to a specific TLS session. In practice this would mean writing your API to issue tokens that can only be used in the same TLS session. This way, if an API token is compromised from a client, an attacker can’t move that token to another client or machine because they would have a different TLS session for the initial issuer. This still isn’t foolproof but the work and effort for the attacker just multiplied.

## Key Takeaways for Managing API Credentials

In closing, here is my best advice for managing API credentials:

* Never paste a secret into your code. Never ever!
* Secure your API using OAuth 2.0 by writing your API to act as an OAuth 2.0 “Resource Server”
* Use JSON Web Tokens (JWT) as your tokens to embed additional context
* Use the token as a Bearer token with the Authorization header to prevent leaking your token in logs and caches
* Implement regular token rotation to reduce the damage from leaked keys, poor practices, honest mistakes, and disgruntled employees.
* Monitor your source code for token leaks
* Implement “channel binding” to tie your API tokens to the TLS session they are requested over
