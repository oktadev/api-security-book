# API Gateways {#gateways}

<div class="chapter-author">By Keith Casey</div>

An API gateway is a firewall that sits between your API and your users. They range from the simplest proxies which apply throttling and white/blacklisting to fully configurable platforms with fine-grained access mapping individual permissions to specific HTTP verbs and endpoints. Realistically, using an API gateway is not necessary but it makes some things faster, easier, and more reliable, which allows you to focus on your API.

*The most prominent gateways are [Google’s Apigee](https://apigee.com/api-management/), [Salesforce’s MuleSoft](https://www.mulesoft.com/), the [AWS API Gateway](https://aws.amazon.com/api-gateway/), [Microsoft Azure’s API Management](https://azure.microsoft.com/en-us/services/api-management/), and the [Kong API Gateway](https://konghq.com/) but the most appropriate gateway for your project will vary depending on context, use cases, and budget. This section does not make a recommendation for a particular gateway but describes the process and use cases where one may fit.*

Most API gateway vendors call themselves API management platforms because gateways are just one part of an overarching API management strategy. With that in mind, there are five key things that most API management platforms provide:

1. Lifecycle Management
1. Interface Management
1. Access Management
1. Consumption Tracking
1. Business Goals

When you’re building and deploying your API, you need to address each of these five areas, which is one of the main reasons API management platforms have taken off in recent years: they make solving these problems tangibly easier.

## What Does an API Management Platform Manage? {#gateways-manage}

### Lifecycle Management

Effective API management begins long before your first HTTP request. In fact, it likely begins in a document or at a whiteboard with a simple requirement and business needs. It quickly turns into a specification and then a workflow and then a data and interaction model. Eventually, it probably turns into an Open API specification, deployable code, and metrics that are carefully tracked by the team.

The key technical aspect of this entire process is understanding what stage the API is in, how and where it’s deployed, and how it’s maintained. As a result, many of the gateways integrate with the cloud hosting services blend directly into your devops processes. Further, the process and these integrations are the same whether the APIs are destined for internal, partner, or even consumer use. In this area, that distinction is irrelevant.

### Interface Management

While it’s pedantic to note, the “I” in API stands for Interface, and it’s the only interface our users will ever interact with. A full discussion of API design is beyond the scope of this book, but the result will always be individual URLs or endpoints we need to support, which HTTP verbs are applied and how, and what parameters or properties are required for each.

API management platforms do not help you choose specific endpoints or which verb is best. Instead, once you’ve made those decisions, the API gateway will allow you to map external URLs to specific endpoints in your API and whitelist specific HTTP verbs, parameters, and even datatypes each endpoint supports. As the first line of defense, this is one of the subtly powerful aspects of a gateway because it limits the surface area where your API can be attacked. That said, all the standard data sanitization practices still apply.

More advanced API management platforms go a step further. Instead of merely configuring the available endpoints via a web interface or even an API, you can upload your API specification document - like Open API, Blueprint, or RAML - and the gateway will parse the document and configure the external interface. You may even be able to integrate this with your continuous integration system to automatically deploy your API into staging, perform the appropriate checks, and prepare to deploy to production.

### Access Management

Access management in an API management platform is where things begin to get more complex. Until this point, the platform has dealt with deeply technical challenges the solutions to which are expressed entirely in code. Access management - both authentication and authorization - is a combination of code, the context of the user and their use case, and business policies and practices. This makes it fundamentally more complicated.

At the simplest level, every gateway can use an API key that is checked on every request. While this works, it lacks the fine-grained access most security and compliance teams require.

As an alternative, many API gateways also include a basic OAuth 2.0 server. Your users perform a simple OAuth user flow, receive an access token, and can then use the API. There are two significant drawbacks to this approach. First, the gateway has to keep its own list of users and activate or deactivate them as employees come and go. As yet another independent system, it’s easy for this information to get out of sync. The second drawback is that security teams can’t review, audit, and validate the security policies implemented by the gateway. For some organizations, this is troubling at best and catastrophic at worst.

The final approach gateways take is to provide a pluggable interface for an external Identity Provider (IdP). Using an IdP is an easy way to integrate user management with a more extensive system such as Active Directory or Okta’s Universal Directory. The single biggest benefit is simplicity: users are activated or deactivated in the API gateway as they are activated or deactivated in the directory. For internal or employee-oriented scenarios, this resolves a major security requirement. Further, by centralizing the issuance of access tokens, the security team can audit and even control the policies independent of API development.

### Consumption Tracking

As we move further away from the hard technical implementation and into the business concerns and requirements, the next area is consumption management, or more fundamentally onboarding and engagement. To expose these functions, most API management platforms include a developer portal for documentation and samples, a logging page to show usage and errors for debugging, and sample code to show how to use the API. Every component here has exactly one goal: How can a developer get started with and use your API successfully?

### Business Goals

The final aspect that an API management platform addresses are the business goals. On a technical layer, this overlaps with the consumption aspects to track overall API usage but provides a more detailed look at business analytics. Therefore, it’s not just overall API consumption but identifying which API calls are the most and least important and how they map to revenue. The most advanced gateways will also include integration with web analytics platforms to track and understand where your users are struggling on setup and configuration.

## What Problems does an API Management Platform Solve? {#gateways-solve}

An API management platform or an API gateway makes basic security easier. With a well-configured gateway, you know exactly which endpoints are open to the world and what parameters they expect. You still have to filter and validate the input according to best practices, but the attack surface is a fraction of what it would be otherwise.

An API management platform handles traffic shaping. Bad actors will misuse and abuse our API. There will also be people who make honest mistakes and run an infinite loop, as well as customers who are really excited about the service. Any one of those can take down our API or drive costs astronomically high. Regardless of the reason, we need to be able to throttle and stop traffic before it hurts us and our customers.

An API management platform lets you worry about other problems. Most teams have enough problems designing, building, documenting, demonstrating, and marketing an API. When we can hand off essential components to reliable third parties, we have to consider it so we can do all the tasks unique to us.

Finally, an API management platform is excellent at logging. One of the biggest challenges for both your customers and your team is understanding “what happened?” The gateways will capture everything and most present it in a clear, consistent manner. A good debugger will save developers - both internal and external - hours of effort and frustration.

## What Problems does a API Management Platform not Solve? {#gateways-not-solve}

An API management platform does not design your API. You still need to understand your users, their goals, and the best way to accomplish those goals. That will require you to determine which use cases you are and are not solving with your API. Further, you have to decide the name and structure of appropriate endpoints and what is required to interact with them.

An API management platform is also not a universal security solution. While it limits your attack surface to pre-defined endpoints with specified parameters, those endpoints still need adhere to security practices regarding data filtering, rate limiting, authentication, and authorization. Various API breaches - such as Equifax and Panera - resulted from attackers using published endpoints in unexpected ways to download entire customer lists, transaction history, and complete credit reports. Rate limiting would have slowed these attacks and monitoring may have detected them, but only strongly defined and enforced authentication and authorization could have stopped them.

The success of your API is also not driven by your API management platform. From a technical perspective, your API still has to be stable and reliable. From a product/market fit perspective, your API still has to solve an important problem for a measurable customer base. And finally, from a user experience perspective, your customers need to find your API and be able to get started quickly enough to solve their problem.

And finally, an API management platform will not establish governance policies for you. When large companies begin an API program or begin to coordinate API efforts, you have to create and enforce policies for tracking APIs lifecycle and development, consistent and predictable naming of endpoints and parameters, understanding and applying security procedures, and publishing them for each audience. These are all leadership and management issues you need to consider in addition to API gateway.

## Which API Management Platform is the Best? {#gateways-the-best}

This is a much more complex question and depends on your use case, budget, and familiarity. If your infrastructure is entirely on AWS, Azure, or Google Cloud, using their respective gateways is a safe choice.

If you are like most organizations and have various components and systems in various places, the decision becomes more challenging. Mulesoft has its roots in the Enterprise Service Bus (ESB) area, so it is ideal when you have to wrap existing systems and orchestrate components into a single interface. Apigee was born and lives entirely in the cloud so if your architecture is entirely in the cloud or you’re just starting, it may be a better fit. Alternatively, Kong and Tyk.io are self-hosted open source gateways which will allow you to deploy them on nearly any architecture. If you’re deep into microservices, they may be a better approach to embed directly into the microservice.

Regardless, the original constraints around access management don’t go away. Having a central place to create, manage, audit, and deploy access and security policies is key to knowing your people and systems have the right access to the right systems for the right reasons for the right amount of time. Distributing that aspect across servers, teams, and codebases is confusing at best and catastrophic at worst.

