# Totally Custom Resource

So far, we have *two* API resource classes: `DragonTreasure` and `User`. Both of
these are *entity* classes. But having your `#[ApiResource]` on an entity class
*isn't* a requirement. You can create any normal boring PHP class you want, add this
`#[ApiResource]` attribute to it, and wham, *bam*! It becomes part of your API.
*Well*, there would be *some* work left, but we'll see that in a moment.

Why *would* you want to create a custom class for your API instead of using an
entity? Two main reasons. First: because the data you're serving *doesn't* come from
the database... *or* it comes from a mixture of *different* database tables. And
*second*: the data you're fetching *is* coming from the database... but
because your API looks different enough from your entity, you want to clean things
up a bit by having a class for your API *separate* from your entity class. We'll
address both cases, starting with the first: When your data comes from somewhere
other than a database.

## Creating the Class

Here's the situation: each day, we post a unique *quest* for our dragons to complete.
We want to expose these quests as a new API resource. They'll be able to list all
*past* quests, fetch a *single* quest by the date, or update the *status* of a
quest if they complete it. That's pretty easy. *But* we're *not* going to store this
data in the database. We're going to pretend that the data comes from somewhere
else.

Since this data isn't in the database, instead of making an entity, we're going to
create a brand new class and put it in this `ApiResource/` directory. This directory
was added *for* us by the API Platform recipe when we originally installed it...
and it's meant to be the home for your API resource classes. Add a new PHP class...
and let's call it `DailyQuest`.

To make this part of your API, just add `#[ApiResource]` above the class.

That's it! And if we check out the docs... *boom*! It's *already* in our API
documentation! Though, it *does* look a bit odd. Notice that the single `GET`
is missing - normally, there's something like `/api/treasure/{id}` here. We'll
see *why* that's missing in a minute.

## ApiResource Class Directories

Oh, and, by default, to find all of our API resource classes, API Platform scans
just *two* directories looking for this attribute: `src/Entity/` and `src/ApiResource`.
This *can* be configured in `/config/packages/api_platform.yaml` with a mapping paths
config... but I won't show that because we don't need to tweak it.

Okay, so... how could this *possibly*, *already* be part of our API? It's just a
class... and it doesn't even have any properties. Try the `GET` collection endpoint.
Hit "Execute" and... we get a 404. So... it's not *actually* working. If we try the
`POST` endpoint - we're just sending empty data - it returns a 201 status code as
if it was *successful*... but behind the scenes, absolutely nothing just happened.
No data was created *or* saved.

Look back at our favorite "upgrade" page on the documentation: the one that talks
about providers and processors. We know that, if we add the `#[ApiResource]` above
an *entity* class, we get these processors and providers for free. It turns out
that... this is really the *only* difference between adding `#[ApiResource`] above
a random class and adding above an entity. When you use `#[ApiResource]` on an
*entity*, API Platform automatically gives you a processor and provider for all of
your operations. When you create a *custom* class, you have *no* providers and
*no* processors. This means that API Platform doesn't know how to *load* the data
when you make a `GET` request... and it scratches its head when it comes to
*processing* the data at the end of a `POST` or `PATCH` request.

Adding those missing pieces is *our* job! Let's start that *next*.
