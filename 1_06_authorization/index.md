# Authorization {#authz}

<div class="chapter-author">By Sai Maddali</div>

In the previous chapter we discussed authentication and the various ways of authenticating an entity. We learned that authentication *only* deals with validating the identity of an entity. It answers “Who are you?” Thus, authentication doesn’t answer “What are you allowed to do?” That’s the realm of authorization. Once an entity is authenticated into a system, that system needs to understand whether the authenticated entity can access, view, or interact with something.

Let’s consider an example. When you log into your bank account, you're considered authenticated – but that doesn't mean you're necessarily authorized to perform certain actions, like withdrawing funds. Just being logged in doesn’t also allow you to withdraw $1,000,000. This is why when you buy something at the store with your credit card, the card reader says “Authorizing” and not “Authenticating.” It’s trying to verify whether you have enough funds in your account to make a purchase.

In this chapter I'll give you a brief overview of the various types of authorization and how they can be utilized to your advantage when building API services.

## Types of Authorization {#authz-types}

While each authorization type has its pros/cons, the goal is to convey when to use one  scheme over the other. We will cover hierarchical, role-based, attribute-based, and delegated authorization.

### Hierarchical

Hierarchical authorization is exactly what it sounds like — authorization determined based on the hierarchy. As you might imagine, within this structure permissions are determined by an entity’s place in the hierarchy.

One good example of this is organizational hierarchy. Imagine Wookie Inc. is a social music streaming service specifically for hip hop listeners. Beyond music streaming, it also has its own publishing business that discovers new artists and manages them.

It has the following divisions: Marketing, Talent Management, Engineering, and Services. All of these main divisions have the following sub-divisions:

* Marketing has product, corporate, and partner marketing
* Talent Management has recruiting, contracts, and communications
* Engineering has infrastructure, QA, devops, and core engineering
* Services has support, customer success, and professional services.

In this hierarchical authorization model, the leader of the infrastructure engineering sub-division, should have access and view into everything in that infrastructure org but not necessarily the other sub-orgs like QA/devops and vice versa. Similarly, if we go a level above, the CTO leading the engineering org should have access to that entire org which includes all the sub orgs only in Engineering. Rinse and repeat.

Although it sounds simple, modelling authorization with a hierarchy is not an efficient model unless your app is really simple with barely any authorization decisions to make. Making authorization decisions in this model require expensive recursive database lookups. The most common queries – such as “is this employee in this group?” – require exploring a significant portion of the organization structure and the problem only gets worse as the organization grows.

Essentially, hierarchy is just an organizational chart, not a workable authorization model. It’s important to think of hierarchical authorization that way because it's rarely a scalable or complete solution. Every system needs to understand the different roles that can use that system and everything that they are allowed to do within that system.

### Role-Based Access Control
Many organizations have roles and responsibilities that don’t quite fit a strict hierarchical structure. For example, a release manager on a development team may have access to deploy their components but their direct supervisor may not. Let’s take a look at role-based access control, starting with a simple use case: one user creating another user.

Before we dig into implementation, there are a few questions to consider:

* Who can create the user?
* Where in the hierarchy can the user be?
* What user type can the user be?
* What permissions can the new user have?
* How do I model those permissions?

There are multiple ways this problem can be solved. We can pick a single user or group of users and only give them the permissions to create users and do other administrative tasks. But this is not a scalable approach. We need to have a more generic model for user-types and permissions. There are various approaches to tackle this but we will focus specifically on role-based access control or RBAC and attribute-based access control or ABAC.

To solve this problem, our hypothetical company, Wookie Inc., hires their first IT person, Han. He’ll take care of all IT-related tasks. Because Han is an IT admin, he has the rights and privileges to create other users, delete them, and assign them to the right org/department.  As usual, IT admin is the role type and the privileges are the permissions.

Similarly, engineering has Jr/Sr engineers, architects, development managers, and product managers and they all have different privileges. Junior engineers rarely have permission to touch production systems. Architects may have access to specific instances in AWS. Dev managers can change the status of ticket on JIRA. Similar responsibility boundaries and policies exist in every group of every organization.

The roles in an RBAC model are the de facto way of determining what permissions a user or service has. The great thing about RBAC is it enables you to apply both broad and granular access policies. You can use various objects like groups and scopes to make implementation easier.

You define different roles, what those roles can do, and assume some combination of them to users and you’re done!

RBAC not only gives great control over how to manage access but is also a great model for auditing access. Once we create a consistent management framework it becomes easier to answer the question: “who has access to what?” RBAC creates a logical model that reflects the structure of system and its responsibilities.

Unfortunately, RBAC still has drawbacks. Imagine a company with 100k employees and thousands of roles with specific permissions or a microservices architecture with thousands of services, each needing fine-grained access to features and functionality of other microservices. Thousands of services each of which has its own unique set of permissions for how and when they can interact with each other. Building for these scenarios with RBAC introduces a lot of complexity:

* The number of roles will explode making management a nightmare.
* The scale makes it hard to validate and audit access. Nothing can stop me from accidentally assigning a user or service to the wrong group giving them wrong privileges.
* To handle this scale, roles become more generic and apply to many users or many services.

And it still doesn’t address user specific data.

For example, let’s say I am building a banking application. Only bankers who are temporarily authorized by the customer can carry out certain actions for that customer. How can this be enforced in my API? The banker role is generic but the authorization is user specific. The approval condition can be modelled as a user attribute and map to specific actions. These actions could be anything from withdrawing, reading balances, adding account admins, etc.

### Attribute-Based Access Control

The pattern must be obvious by now – in ABAC, access is defined by the attributes on the user or service, and a policy that enforces what actions these attributes are allowed to perform. As we saw in the above section, implementing RBAC is relatively simple but maintaining it over time becomes cumbersome as the system grows and permissions get more fine-grained. With ABAC, it’s the opposite. Implementing it can be a herculean task but once complete, maintaining it is relatively simple and efficient.

With RBAC, every action that can be carried out in a system is tied to a complex set of role combinations. If a new role is introduced, every action that this new role can access must be updated. However with ABAC, you can create a central policy engine in which you define complex boolean logic for what attributes are allowed to do based on various conditions. ABAC is a great model for fine-grained (deep/specific) access control whereas RBAC is good for coarse-grained (broad/generic) access control.

It’s also important to note that attributes can be about anything or anyone:

* There can be user or subject attributes like name, ID, role, department, status
* There can be attributes to actions like CRUD: add, edit, remove, approve
* There can be resource attributes like the bank example we covered earlier: bank account, bank balance, or resource clarification like top secret, public access
* There can also be attributes about the context of an interaction: time, location, device

Let’s revisit the Wookie Inc. example from the RBAC section. We created a simple RBAC management model for an IT admin As the organization grows and enters new markets, the access requirements also go up, introducing all sorts of complexity:

* HIPAA regulations specify only HIPAA certified users can look at user data
* To handle scale, Wookie Inc. decides to move to a microservices architecture
* Other compliance requirements mean that Wookie Inc. has to have a way to audit everything, including APIs.
* They have decided to open up some of their APIs for public access.

All of these requirements aren’t uncommon for companies to address as they grow. Let’s take a deeper look at the last one on the list: exposing APIs for public consumption. This can be third party developers that want to build against the platform or customers that need to build custom workflows. Either way, making sure the right user has the right privileges is incredibly important and difficult to implement. Unlike a product where actions are simple and what a user can do is based on roles, API actions are more granular.

This adds more complexity to API authorization Take a simple music playlist API, can the consumers of my API:

* Read what songs are in a users playlist?
* Add or delete songs from a playlist?
* Change the description?
* Change the playlist order? Sort it?

Even in the simplest of scenarios, authorization can get complex quickly.

All of these actions can be modelled as attributes of the resource that’s being accessed. For example, being able to add, edit user details are attributes of the user resource. When writing code, I need a better way to model these attributes. We can utilize scopes here. If you recall, back in the Authentication chapter, OAuth 2.0 had the concept of scopes. We can use that model again here:

* User scopes: read:name, edit:name, read:email, edit:email
* Playlist scopes: read:playlist, edit:playlist, edit:description, sort:playlist

The naming convention can be anything you prefer. You could also model some of these as read_name, edit_name, etc.

We now have a model for all the resource types and the various actions that can be carried out on these resources. You can also take this a step further and define more granular attributes: playlists can be public or private. Songs can be explicit or non-explicit.

We can repeat the same model for users also. Users can:

* Be Account admins
* Be in listen only mode
* Be paid subscribers
* Be the primary account holder
* Have genre preferences.

These attributes can be defined on the user object however you like:

```
/users
    id
    Name
    admin
      yes or no
    subscription
      free, family, premium
    /preferences
    /profile
    /playlists
```

These are all user attributes and I can model the actions on them using scopes in a similar fashion:

* `read:account_status`, `edit:listening_mode`, `edit:genre_preferences`
* `edit:subscription`, `read:subscription`, `read:genre_preferences`

At the same time, you can use these user attributes to enforce access:

* Access to social groups in Wookie Inc. can be enforced based on genre preferences or subscription status
* Allowing users to skip songs and that can be based on their mode and subscription status

Using this model, you can now architect a simple model for making authorization decisions. ABAC has a standardized architecture that we can use. Let’s take an simple example: Leia is a free user that wants to edit Han’s workout playlist. Editing playlists depends on a simple factor: It is only available to paid members.

How would this flow?

There are multiple ways to architect this but ABAC proposes the following architecture:

* Policy Enforcement Point (PEP) - Think of this as a gateway. Its protecting all the resources and all requests are routed to this point to make a decision. It takes the incoming HTTP request and creates an authorization specific request.
* Policy Decision Point (PDP) - This is really the brain of the architecture. It consumes the authorization request sent from PEP, breaks it down, and evaluates all the attributes: Who is accessing? What attributes do they have? What are they requesting? With all this data in hand, it can consult various sources like a database or a 3rd party system like Okta or LDAP to make a decision.

Using this, let’s see how Leia’s request to edit Han’s playlist will be evaluated:

Leia presses the “Edit Playlist” Button → Request is routed to PEP → PEP constructs an authorization request → PEP requests edi:playlist, edit:description scopes along with some identifying information like user id → PDP uses this information to lookup policy, user info, and returns allow or deny.

While this is a simple example, from it we can extrapolate more complex requests and apply the same architecture for an entire system. For APIs, the PEP is usually the API Gateway. This gateway can then rely on an internal policy engine or potentially using information from a 3rd party identity provider to act as the PDP.

ABAC is a powerful and flexible approach for API authorization that requires an early investment but scales efficiently as your requirements and use cases As a general rule of thumb to decide if ABAC is better than RBAC, estimate how granular your authorization must be. Starting with RBAC for a limited set of roles and actions is a safe choice but as the number of roles and permissions increase, ABAC becomes a better choice. ABAC is also great at establishing governance policies and implementing legal/data protection compliance requirements.

The OAuth 2.0 framework is specifically designed for ABAC that works for many use cases, especially for APIs. When you combine it with tools like JWTs and OpenID Connect, you have a token which represents an authenticated user, additional context information such as their profile, and the scopes to which they have authorization. The OAuth 2.0 extensions allow you to implement RBAC + ABAC and scale as your API and use cases grow.

## Key Takeaways {#authz-takeaways}

In closing, here’s some simple advice on how to think about authorization in your APIs:

* Estimate the scopes or permissions required for your users to accomplish the use cases your API addresses early on
* Keep things simple. Don’t overwhelm yourself or your app with unnecessary overhead. Most applications don’t have complex authorization needs
* Building authorization is hard. For simple scenarios with a few user types and authorization decisions, your impulse will be to build it yourself. Unfortunately, requirements and policies almost always get more complex so this becomes less sustainable over time. Use a third-party authorization service, like Okta, whenever possible
* Even with a third party provider in place, it’s still important to understand authorization so you can make good decisions on how to architect your application
* RBAC is enough for many use cases. ABAC is the next step
* OAuth 2.0 is an authorization framework that you can leverage for most scenarios
* Log everything. Authorization decisions must be reviewed and adjusted based on new use cases, usage patterns, and bad actors. Auditing becomes more important as you grow

