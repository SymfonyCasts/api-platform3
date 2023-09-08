# User Class Dto

The *fastest* way to get started with API Platform is by adding these `#[ApiResource]`
attributes above your entity classes. That's because API Platform gives you free
state providers that query from the database (which include pagination and filters)
and free state *processors* that save things *to* the database.

## To use DTOs or Not?

*But*, as we've seen with `DailyQuest`, that's not *required*. And if your API starts
to look pretty different from your entities - like you have fields in your API
that don't exist in your or are named differently - it might make sense to *separate*
your entity and API resource classes.

Right now, our entities *are* API resources... and that *has* added some complexity.
For example, we have a custom `isMine` field which is powered by this
`isOwnedByAuthenticatedUser` property: a non-persisted property that we populate
via a state provider. And one of the most *noticeable* things is our huge use of
serialization groups. We *have to* use serialization groups, like `treasure:read`,
so that we can include the properties we *want* and avoid the properties that we
*don't* want.

This *has* saved us some time... but increased complexity. So let's get *crazy*
and use a dedicated *class* for our API *from the start*. That's often referred to
as a "DTO", or "Data Transfer Object". I'll use that term a lot - but it just means
"the dedicated class for our API" - like the `DailyQuest` class.

## Removing the API Stuff from User

To kick things off, let's remove *all* of the API-related stuff from the `User` entity.
Remove the `#[ApiResource()]`... both of them, filters and validation. You *may*
still want validation constraints if you're using your entity with the form system...
but since we're not, let's clear it. I'm also clearing anything related to
serialization... and hunting down anything that's hiding.

Woh. This class is *a lot* smaller now. It think that's everything... the use
statements on top look good... and... *awesome*.

Let's also remove the state processor for `User`, which hashes the plain password.
We *are* going to re-implement many of the things we just deleted, but I want to
start with a clean look at things.

Alright, go check out the API docs. We're reduced to "Quest" and "Treasure". I
love it!

## Creating the DTO / Dedicated ApiResource Class

Ok, we're going to start like we did with the `DailyQuest`. In the `src/ApiResource/`
directory, create a new class called `UserApi`... to indicate this is the *user*
class for our API. Inside, add `#[ApiResource]` above it.

So far, this is just like any other custom API resource. It shows up in the docs...
and if we try the `GET` collection operation, it fails with a 404. Heck, we're
even missing the "ID" part in the URL of the item operations.

To fix that in `UserApi`, add a `public ?int $id = null` property... because our
users *will* still be identified by their database id. Oh, and I'm using a public
property *just* to make life easier... and this class will stay simple.

As soon as we do that... API Platform *recognizes* that this as the identifier, and
our operations are *looking good*.

While we're here, let's also modify the `shortName`. This is called `UserApi`, which
sucks as a name - so change it with `shortName: 'User'`.

Suddenly, this is *starting* to look like what we had before!

The *big* missing pieces, like with `DailyQuest`, are the state provider and state
processor. Let's add the state provider next.... but with a *twist* that leverages
a brand-new feature that's going to save us a *ton* of work.
