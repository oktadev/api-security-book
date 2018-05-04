# Transport Layer Security {#tls}

<div class="chapter-author">By Dave Nugent</div>

Any discussion of API security, and more broadly security online, has to start with an understanding of Transport Layer Security (TLS) and its cryptographic underpinnings. Transport security adds privacy and integrity for messages between two parties. In common usage, it’s the ability to transmit data over a network without exposing that data to untrusted third parties.

Transport security is critical to modern internet infrastructure, where machines on the public internet exchange sensitive data such as passwords, personal information, financial transactions and classified material. Without efficient and effective transport security, these transactions would be tedious or impossible to complete over a shared network.

The rest of this book dives into detail about best practices for securing your APIs. This chapter focuses on the necessary first step of being able to communicate securely over a network. We’ll also cover common pitfalls and best practices for securing your data in transit.

## A Brief History of Secure Data Transport {#tls-history}

Today we take for granted our ability to send data over the internet securely. When we purchase an item from Amazon or complete our taxes online, we place our trust in the infrastructure of the internet to securely share our data with only the systems and parties we intend.

<figure id="fig_tls_siggaba">
  <img src="__DIR__/images/SIGABA-labelled-2.jpg" alt=""/>
  <figcaption style="font-size: 0.8em;">The SIGABA, a cipher machine used by the US during World War II</figcaption>
</figure>

Historically, secure information exchange wasn’t simple. Ciphers and cryptography were used in ancient times to encode sensitive messages. These schemes generally had to two main weaknesses: they relied on a shared code book or cipher to encode/decode the messages, and third parties could decode them through pattern analysis.

The problem of a shared code book or cipher persisted for millennia. To ensure that each party would be able to encode/decode the message successfully, a secret “key” needs to be exchanged, for example by courier or diplomatic bag. By working off the same key, the parties to the message exchange would then be able to encode/decode their data. However, if a nefarious actor were able to gain access to the secret key (for example, by bribing the courier) then all past and future communication could be compromised.

## How Does Key Exchange Work Today? {#tls-key-exchange}

Current hybrid cryptosystems like SSL/TLS use symmetric key algorithms (they are generally faster than asymmetric algorithms.) Symmetric key algorithms require a shared secret, exchanged via key-exchange algorithm.

The most famous cryptographic protocol for key exchange is Diffie–Hellman, published in 1976 by Whitfield Diffie and Martin Hellman. Diffie–Hellman allows the creation of a shared secret between a sender and receiver. This shared secret is unable to be deduced by an eavesdropper who is observing the messages between the sender and receiver, except via a brute force attack. If the keyspace for the shared secret is large enough and the secret generated is sufficiently random, brute force attacks become nearly impossible.

## Acronym Party: HTTPS/SSL/TLS {#tls-acronyms}

Now that we’re up to speed on the basics of key exchange, let’s discuss some of the acronyms you’ll see throughout this discussion:

* **HTTPS**, also called HTTP over SSL/TLS, is an extension of HTTP which encrypts communication. HTTPS URLs begin with “https://” and use port 443 by default. This is an improvement over HTTP, which is vulnerable to eavesdropping and man-in-the-middle attacks.
* **SSL** or Secure Sockets Layer was released by Netscape in 1995. SSL adoption increased after the redesigned SSL 3.0 was released in 1996. The IETF prohibited SSL 2.0 in 2011. SSL 3.0 was prohibited in 2015 after the IETF identified various security vulnerabilities which affected all SSL 3.0 ciphers.
* **TLS** or Transport Layer Security is the successor to SSL. In fact, the documentation for TLS 1.0 describes it as an “upgrade” of SSL 3.0. The current TLS version is 1.3. Although virtually all HTTPS-secured traffic uses TLS due to problems with SSL, the SSL nomenclature persists in internet culture. These days, when somebody says SSL, it is likely they mean TLS.

In this article, I use “SSL/TLS” to avoid ambiguity that the term SSL causes. However, your implementations should always use TLS.

## How Does SSL/TLS Work? {#tls-how}

When an SSL/TLS connection needs to be made between a client (a browser, or an application attempting to access an API) and a server (a web server, for example, or an API endpoint) it follows a number of steps. The TLS spec, published by the IETF Transport Layer Security Working Group, gives an overview of these steps.

Here is a visual representation of how the client or “sender” and server or “receiver” set up an SSL/TLS connection:

<figure id="fig_tls_">
  <img src="__DIR__/images/" alt=""/>
  <figcaption></figcaption>
</figure>

Let’s walk through the steps at a high level:

**TCP Connection**

Your client (browser or application) will initiate a TCP connection with the server. Your client and server can exchange information once this connection is established. Although TLS can work over different transports, by far the most common use case is over TCP/IP, due to its ubiquity, reliability of transport and ability to recover from lost packets and transport errors. In the diagram, SYN, SYN ACK, and ACK denote this sequence of events.

**SSL/TLS Handshake**

The SSL/TLS handshake takes place once a TCP connection is established.

**ClientHello**

The client sends a “ClientHello” message, which lists the versions of SSL/TLS the client is capable of, what ciphersuites it has available, and any compression types available.

**ServerHello and Certificate Response**

The server responds with the same information as the client, and sends the server’s certificate back to the client as well.

**Certificate Verification**

The client verifies that the certificate is valid, and also verifies that the server is authentic and not an impersonator conducting a man-in-the-middle attack. For more information about how certificate verification is accomplished, see “SSL/TLS Certificate Verification” later in this chapter.

**ClientKeyExchange**

In the previous section, we discussed key exchange, with the example Diffie–Hellman algorithm. This is the step of the handshake where the key exchange actually happens.

**Finished/Application Data**

Now that the handshake has been established and keys have been exchanged, information may be encrypted and decrypted between the client and server based on the shared secret which has been generated. This symmetric cryptography will secure the remainder of application-to-application communication.

## Exposed Data over SSL/TLS {#tls-exposed-data}

TLS aims to provide data integrity and privacy between two trusted parties. Information exchanged over the latest version of TLS should be secure from being exposed to third parties in unencrypted form. Additionally, third parties should be unable to modify that information: this is the concept of data integrity, and is the reason an integrity check is performed on each message.

However, even though application data transmitted over a properly-established TLS connection is secure, some metadata and connection information is necessarily exposed to third parties. Without additional obfuscation outside of the scope of SSL/TLS, an observer will be able to discover:

* **The IP addresses of the client and server.** Since the client and server are communicating over TCP/IP, which operates at a lower level than the TLS protocol, these IP addresses are public and are used for routing the encrypted packets.
* **The server certificate**, including the server name. Since the server sends the certificate to the client as a part of the handshake process before encrypted messages are sent, an observer of the handshake will see the certificate in plain text.
* **The approximate length of the URL and payload.** Although the application data is encrypted, an astute observer of an HTTPS connection will be able to deduce the length of the URL requested and the approximate size of any non-cached assets. Cached assets, since they reside on the client already, are not vulnerable to this introspection until the cache expires and they are re-fetched.

In addition to the data listed above, additional information may be inferred based on the timing of network requests. Outdated SSL/TLS versions have additional identified vulnerabilities, and in the future one must anticipate the TLS spec will be versioned to ameliorate any vulnerabilities identified in the future.

## SSL/TLS Server Certificates {#tls-server-certificates}

You’ve seen that SSL/TLS server certificates are integral to the SSL/TLS handshake. They help the client verify that the server is who they appear to be, which helps prevent third parties impersonating the server. But what are certificates, anyway? How are they generated, and why do the clients trust them?

SSL/TLS Server Certificates are small data files that encapsulate information about the server that owns the certificate. This information is verified through a chain of Certificate Authorities that bridge the gap between the authorities that the browser trusts and the authorities that trust the server.

### Certificate Generation

Private or self-signed SSL/TLS certificates are trivial to create. OpenSSL, available for most platforms, allows users to create self-signed SSL/TLS certificates. These consist of a private key and a public key, which a client and server can use to encrypt data and exchange it securely.

The downside to self-signed certificates are that they provide no guarantees of the server’s identity. This may not be an issue for corporate networks, where certificates can be exchanged and trusted through internal provisioning.

### Trusted Certificates

If anybody can create and self-sign their own certificate, then how is a client (be it a browser or an application hitting an API endpoint) able to verify a server’s identity?

On the public internet, trusted certificates are required. Historically, generating these trusted certificates could be expensive. Trust and identity on the web works similarly to meeting individuals in the real world. If somebody wants to verify your identity in person, they may ask for a photo ID generated by a trusted third party, like a government, school or corporate institution. The person verifying your identity trusts that this third party has verified your identity and that your ID cannot be forged.

This is called a chain of trust, and certificates are verified on the web in the same way. Each client has a list of third parties that they trust to verify certificates. These are called root certificate authorities. Microsoft, Oracle, Mozilla, Adobe and Apple maintain lists of trusted root certificate authorities through their own root programs and include these lists in the operating systems and browsers they produce.

Recently there has been an effort to spread adoption of HTTPS by making generating and installing certificates as easy as possible. [Amazon Certificate Manager](https://aws.amazon.com/certificate-manager/) and [Let’s Encrypt](https://letsencrypt.org/) are two certificate authorities who make it easy to create and manage trusted certificates for free.

## SSL/TLS Certificate Verification {#tls-certificate-verification}

Now that we understand the importance of trusted certificates and why certificate authorities are necessary, let’s walk through the missing middle step: how a client verifies a server’s SSL/TLS certificate.

First, the client gets the server’s certificate as part of the SSL/TLS handshake. (If you are writing an application that is hitting an HTTPS API endpoint, this step happens before any application data is exchanged.)

The client checks to ensure that the server’s certificate is not expired and that the domain name or IP address on the certificate matches the server’s information. Then, the client attempts to verify that the server’s certificate has been properly signed by the certificate authority who authorized it. Due to the nature of asymmetric encryption, the client is able to do this using the information within the server’s response -- without even contacting the certificate authority.

It’s unlikely that the server’s certificate is signed directly by a root certificate authority that is trusted by the client. However, the client can trust any number of intermediate certificate authorities, as long as the trust chain eventually leads back to one of the client’s trusted root certificates:

<figure id="fig_tls_certificate_chain">
  <img src="__DIR__/images/certificate-chain.png" alt=""/>
  <figcaption>Illustrating the chain of trust from a root CA through an intermediate certificate</figcaption>
</figure>

For each intermediate certificate, the client completes the same process: it verifies the issuer’s name matches the certificate owner’s name, and uses the signature and public key to verify that the certificate is properly signed.

Eventually, in a successful transaction, the client will come to a self-signed root certificate that the client implicitly trusts. At this point, the client has built a cryptographic chain of trust to the server, and the SSL/TLS handshake can proceed.

## SSL/TLS Best Practices {#tls-best-practices}

Hopefully this chapter has convinced you of the ease and importance of implementing SSL/TLS into your public internet application infrastructure. However, even when using SSL/TLS, organizations can be subject to compromise if best practices are not followed. Let’s go over a few of the big ones:

### 1. Use TLS Everywhere
Having certain pages served over SSL/TLS and some served unencrypted can expose data, such as unencrypted session IDs, to attackers. Similarly, don’t allow TLS content to be exposed via non-TLS pages and don’t mix TLS and non-TLS content on the same page.

### 2. Keep Sensitive Data out of the URL and Cache
URLs can be cached in a client’s browser history or application logs and sent to another HTTPS site if the user clicks on a link. Setting TLS pages to be uncacheable prevents information leakage from the client cache.

### 3. Prevent Exposed Data over SSL/TLS
Ensure browsers and applications only access your site via HTTPS by enacting HTTP Strict Transport Security (HSTS). Servers utilizing HSTS send an HTTPS header in their response specifying that requests to their domain should only use HTTPS. An HSTS-complaint client should then make all future requests to that domain over HTTPS, even if HTTP is specified. This helps protect clients from man-in-the-middle and eavesdropping attacks that could be initiated by the client sending sensitive information by making an unsecure HTTP request. For more information, see <a href="https://www.owasp.org/index.php/HTTP_Strict_Transport_Security_Cheat_Sheet" class="url">https://www.owasp.org/index.php/HTTP_Strict_Transport_Security_Cheat_Sheet</a>.

### 4. Use HTTP Public Key Pinning
While not common in browser-to-web-server communications, HTTP Public Key Pinning is quite useful in API communication. The server will respond with an HTTP header specifying a hash of a valid public key, which helps combat certificate authority compromises. When communicating between two server-side apps, if one server has been compromised and an untrusted certificate authority is trusted, TLS compromise can happen. By having the client download and store a known-valid certificate from the server, the client can "skip" the chain-of-trust verification and instead compare the server's certificate directly to their known-good version, thereby guaranteeing authenticity and preventing any opportunity for man-in-the-middle.

### 5. Only Support Strong Protocols and Ciphers
Ensure your infrastructure uses the most recent stable version of TLS and the latest recommended ciphers. Due to evolving vulnerabilities, preferred ciphers may change over time.

OWASP maintains a nearly definitive list of best practices for SSL/TLS online at <a href="https://www.owasp.org/index.php/Transport_Layer_Protection_Cheat_Sheet" class="url">https://www.owasp.org/index.php/Transport_Layer_Protection_Cheat_Sheet</a>.

## SSL Rating {#tls-ssl-rating}

After implementing SSL/TLS into your API endpoint infrastructure, be sure to run an SSL Rating test to validate your use of the SSL/TLS Best Practices in the section above. While an A+ SSL rating is not a guarantee that your infrastructure is ideally provisioned, any lower rating should raise red flags that you can correct.

To run the SSL Rating test on your public-facing site, visit <a href="https://www.ssllabs.com/ssltest" class="url">https://www.ssllabs.com/ssltest</a>.

Even if you aren’t interested in learning about Diffie-Hellman or Certificate Authorities, you still need to secure your API using TLS/SSL and follow the best practices above.

However, I do encourage you to learn more about the details of TLS/SSL. It can be a daunting task and seemingly never-ending task sometimes, but as with any technology, the more you know about TLS/SSL, the more effective you’ll be at building, testing, and reasoning about using it to secure your API. While once could fill an entire book about this topic, hopefully this chapter helped you learn more about the basics of TLS/SSL or helped fill in some gaps in your knowledge.

