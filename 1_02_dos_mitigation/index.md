# DOS Mitigation Strategies {#dos}

<div class="chapter-author">By Lee Brandt</div>

Unfortunately, there’s no way to *completely* prevent Denial of Service (DoS) attacks. And that’s a problem because they’re a plague upon our industry. Between [botnets consisting of 6 to 30 million bots](https://themerkle.com/top-4-largest-botnets-to-date), [30,000 distinct DoS attacks per year and growing](https://www.securityweek.com/internet-sees-nearly-30000-distinct-dos-attacks-each-day-study), and [11.2 billion connected “things” in use by the end of 2018](https://www.gartner.com/newsroom/id/3598917), this problem is only going to get worse. As a developer, the only thing you can do is attempt to mitigate the probability and effectiveness of an attack. So, how do you do that? First, you have to understand what a Denial of Service attack is.

## What Is a DoS Attack? {#dos-what-is}
A Denial of Service attack occurs when attackers attempt to stop a service from servicing the requests of legitimate users. Most commonly, this is done by flooding the service with requests until it is no longer able to respond as fast as the requests are coming in. By forcing the service to respond to the flood of requests continually, the service denies legitimate traffic.

## Why are DoS Attacks So Prevalent? {#dos-why}
There are several reasons why someone might perform a DoS attack on a network or service. Most frequently, DoS attacks are carried out for profit. There are several ways to make money by staging a DoS attack. For instance, competitors of Amazon might find it beneficial if Amazon’s service were slow or offline. This form of industrial sabotage may encourage customers to search for alternatives, increasing profits for competing businesses who aren’t suffering the effects of a DoS attack.

Another way attackers can profit from DoS attacks is by selling access to compromised computers (called “BotNets”) that can perform large-scale Distributed Denial of Service (DDoS) attacks. There are many places online where people can contract BotNets to carry out DDoS attacks. They can typically be rented and charge based on the amount of time that they slow or disable the target service; costs range from a few dollars for a fifteen-minute attack, to a few hundred dollars for twenty-four hours. Portals known as “booter” portals offer the BotNets for hire.

Yet another way attackers can make money by performing an attack is by hijacking personal information like credit cards and social security numbers during a DoS attack. For instance, if attackers target a payment provider with a DoS attack, they might be able to take advantage of the broken system to exploit a vulnerability that isn’t available under normal circumstances.

It may also be the case that instead of _making_ money from a DoS attack, attackers want to cause their targets monetary losses. For instance, if you write an API that sends SMS messages using a service, you might have to pay a few cents per message sent. If attackers flood your service with a hundred thousand API calls that trigger those SMS messages, the financial loss could be severe.

Also, if an attacker knows that a service provider pays for inbound bandwidth, it might just be a matter of sending the service far more (or larger) requests than normal to eat up available bandwidth resources, causing the victim’s cloud provider to send them a hefty monthly bill. Even a few calls made to a particularly data-heavy service can increase a company’s cloud-based bandwidth costs. For instance, if a website hosts many large video files, repeatedly downloading massive video files will quickly drive up the provider’s bandwidth costs.

Finally, an attack might be carried out for political reasons. This type of attack is akin to a political protest where protesters might block the door of a business that offends their political sensibilities. In 2011, a group known as the [Syrian Electronic Army](https://www.cnn.com/2013/04/24/tech/syrian-electronic-army/index.html) worked to support the government of Syrian President Bashar al-Assad by targeting political opposition groups. In October of last year, the Czech Parliamentary election was the [target of an attack](https://sputniknews.com/europe/201710231058456317-czech-election-hit-cyberattack/) meant to disrupt the counting of votes. Given all these motivations, it’s easy to see why so many API services come under attack.

## What are the Different Types of Denial of Service Attacks? {#dos-what}
There are three main types of DoS attacks:

### 1. Application-layer Flood
In this attack type, an attacker simply floods the service with requests from a spoofed IP address in an attempt to slow or crash the service, illustrated in <a href="#fig_dos_flood" class="figref"></a>. This could take the form of millions of requests per second or a few thousand requests to a particularly resource-intensive service that eat up resources until the service is unable to continue processing the requests.

<figure id="fig_dos_flood">
  <img src="__DIR__/images/attack.png" alt=""/>
  <figcaption>An attacker floods the service from a single IP address</figcaption>
</figure>

Preventing application-layer DoS attacks can be tricky. The best way to help mitigate these types of attacks is to outsource pattern detection and IP filtering to a third party (discussed later).


### 2. Distributed Denial of Service Attacks (DDoS)
Distributed Denial of Service (DDoS) attacks occur in much the same way as DoS attacks except that requests are sent from many clients as opposed to just one, illustrated in <a href="#fig_dos_ddos" class="figref"></a>. DDoS attacks often involve many “zombie” machines (machines that have been previously compromised and are being controlled by attackers). These “zombie” machines then send massive amounts of requests to a service to disable it.

DDoS attacks are famously hard to mitigate, which is why outsourcing network filtering to a third party is the recommended approach. We’ll cover this later on.


### 3. Unintended Denial of Service Attacks
Not all DoS attacks are nefarious. The third attack type is the “unintended” Denial of Service attack. The canonical example of an unintended DDoS is called “[The Slashdot Effect](https://hup.hu/old/stuff/slashdotted/SlashDotEffect.html)”. Slashdot is an internet news site where anyone can post news stories and link to other sites. If a linked story becomes popular, it can cause millions of users to visit the site overloading the site with requests. If the site isn’t built to handle that kind of load, the increased traffic can slow or even crash the linked site. Reddit and “[The Reddit Hug of Death](https://thenextweb.com/socialmedia/2012/01/17/how-reddit-turned-one-congressional-candidates-campaign-upside-down/)” is another excellent example of an unintentional DoS.

<figure id="fig_dos_ddos">
  <img src="__DIR__/images/ddos.png" alt=""/>
  <figcaption>An attacker uses zombie machines to launch a DDoS against the target</figcaption>
</figure>

The only way to prevent these types of unintended DoS attacks is to architect your application for scale. Use patterns like edge-caching with CDNs, HTTP caching headers, auto-scaling groups, and other methods to ensure that even when you receive a large amount of burst-traffic, your site will not go down.

Another type of unintentional DoS attack can occur when servicing low bandwidth areas. For instance, streaming content internationally means that people in certain areas of the world with slow or bad internet connections might cause problems. When your service attempts to send information to these low-bandwidth areas, packets drop. In an attempt to get the information to the destination, your service will attempt to resend all dropped packets. If the connection drops the packets again, your service may make another attempt. This cycle can cause your service’s load to double or triple, causing your service to be slow or unreachable for everyone.


## How to Mitigate DoS Attacks {#dos-how}
Now that you know what DoS attacks are and why attackers perform them, let’s discuss how you can protect yourself and your services. Most common mitigation techniques work by detecting illegitimate traffic and blocking it at the routing level, managing and analyzing the bandwidth of the services, and being mindful when architecting your APIs, so they’re able to handle large amounts of traffic.

### Attack Detection
The first step of any mitigation strategy is understanding when you are the target of a DoS attack. Analyzing incoming traffic and determining whether or not it’s legitimate is the first step in keeping your service available and responsive. Scalable cloud service providers are great (and may even “absorb” a DoS attack transparently) which is fantastic until you receive an enormous bill for bandwidth or resource overuse. Making sure your cloud provider makes scaling decisions based only on legitimate traffic is the best way to ensure your company is not spending unnecessary elasticity dollars due to an attack. Early detection of an attack dramatically increases the efficacy of any mitigation strategy.

### IP Whitelisting/Blacklisting
The simplest defense against a DoS attack is either whitelisting only legitimate IP addresses or blocking ones from known attackers. For instance, if the application is meant to be used only by employees of a specific company, a hardware or software rule could be created to disallow any traffic, not from a specific IP range. For example, 192.168.0.0/16 would allow any IP address between 192.168.0.0 and 192.168.255.255. The rule rejects any IP address outside that range. If the software is only meant to be used by US citizens, a rule could be created only to allow access to US IP addresses. Inversely, IP blacklisting adds a rule to reject traffic from specific IP addresses or IP ranges making it possible to create rules to disallow traffic coming from China or Russia.

It is important to remember that blocking IP addresses in this way may prevent legitimate traffic from those countries. Blacklisting IP addresses is also dangerous in that you may end up blacklisting all users sharing an IP address, even if many of those users are legitimate. For example, what would happen if a bad actor used an Amazon EC2 server instance to attack a host and that host blocked all Amazon EC2 IP addresses? While the attack might stop, all legitimate Amazon users are now blacklisted from accessing the service.

Also, this strategy may not be effective against DDoS attacks or DoS attacks using spoofed IP addresses. In the distributed scenario, there may be zombie computers with IP addresses all over the place. Creating a rule to filter them out may become complicated and untenable. For instance, if an attacker is generating many requests to your service using a single spoofed IP address, when you block that address the attacker can start spoofing a new IP address and continue the attack.

### Rate Limiting
Rate limiting is the practice of limiting the amount of traffic available to a specific Network Interface Controller (NIC). It can be done at the hardware or software level to mitigate the chances of falling victim to a DoS attack. At the hardware level, switches and routers usually have some degree of rate-limiting capabilities. At the software level, it’s essential to have a limit on the number of concurrent calls available to a specific customer. Giving users strictly defined limits on concurrent requests or total requests over a given duration (50 requests per minute) can be an excellent way to reject traffic and maintain service stability. The rate limit is usually tied to the customer’s plan or payment level. For example, customers on a free plan may only get 1,000 API calls, whereas customers at the premium level may get 10,000 API calls. Once the user reaches their rate limit, the service returns an HTTP status code indicating “too many requests” (status code 429).

While rate limiting is useful, depending on it alone is not enough. Using a router’s rate limiting features means that requests will still reach the router. Even the best routers can be overwhelmed and DoSed. At the software level, requests still need to reach your service even if a rate-limit has been reached to serve up a 429 status code. This means that your service could still be overwhelmed by requests, even if your service is only returning an error status code.

### Upstream Filtering and DDS
One of the best mitigation strategies is to filter requests upstream, long before it reaches the target network. Done effectively, your API never even sees this traffic, so any rate limiting policies are not triggered. There are many providers of “Mitigation Centers” that will filter the incoming network traffic. For example [Amazon Shield](https://aws.amazon.com/shield/) and [Cloudflare](https://www.cloudflare.com/) both offer products that allow for protection against DoS and DDoS attacks by checking incoming packet IPs against known attackers and BotNets and attempt to only forward legitimate traffic. Various API gateways have the same capabilities but can also filter based on the requested endpoint, allowed HTTP verbs, or even a combination of verbs and endpoints.

Passing DoS mitigation responsibility to upstream providers can be a great way to reduce liability and risk as mitigation can be incredibly complex and is an ever-changing cat-and-mouse game between service providers and attackers.

These companies typically offer support should your service be currently under attack in an attempt to minimize damages. It then becomes the responsibility of the provider to keep abreast of new DDoS attack vectors and strategies, leaving you to focus on building your service.

### Programming for Scale
With the proliferation of easily-scalable cloud services, it’s easy to become lazy and not think about efficient development patterns. Sometimes it’s easy to spot DoS-vulnerable parts of your application while other times it’s not so apparent. It’s vital to offload resource-intensive processes to systems that are designed to handle those operations. In some cases, you may even be able to queue expensive work for later batch processing, reducing DoS attack surface area. For instance, uploading or encoding images or video can take a lot of processing power, and it’s essential that your application is not affected by those processes. In some cases, a well-configured cache — at the network or application level — can return data previously processed and unchanged. After all, the fastest processing possible is the processing you don’t have to perform.

Sometimes, when a startup is first creating their product, the team pays less attention to performance and more attention to shipping features. While this can be okay early on, as a service becomes popular, it’s hard to go back and fix performance issues before they cause a widened surface area for attackers. It’s good practice to make performance testing part of the development cycle and continuous integration process. By running the [Apache Bench command](https://httpd.apache.org/docs/2.4/programs/ab.html), you can get basic performance information about your service. You can also use AB to write automated tests that simulate many users and check that your service responds to requests within a specified time. These performance tests can be run during the continuous integration process to ensure the application code performs at a level that is satisfactory to your organization.

## Parting Shots {#dos-conclusion}
API services are becoming a more and more critical part of the overall world economy, and attacks on API services is on the rise. Whether your services are targeted for fun, profit, or political reasons, it’s important to know how to protect your assets (and those of your customers). Mitigating the technological and economic effects of a DoS on your internet-based resources is critical to the success of your company’s platform.

Using techniques like IP whitelisting and blacklisting, upstream filtering, rate limiting, and good programming practices is your best defense against would-be attackers. It’s important to keep abreast of changes in the security landscape and to make performance and load testing a part of your everyday software delivery practices. Whenever possible, offload the responsibility to companies that specialize in those practices so that you can focus on delivering value to your customers.

Overall, build great stuff… and be careful out there!
