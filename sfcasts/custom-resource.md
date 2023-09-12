# Totally Custom Resource

So far, we have *two* API resource classes: `DragonTreasure` and `User`. And both
are *entity* classes. But having your `#[ApiResource]` attribute above an entity class
*isn't* a requirement. You can create any normal boring PHP class you want, sprinkle
this `#[ApiResource]` attribute on top, and wham, *bam*! It becomes part of your API.
*Well*, there is *some* work left, but we'll see that in a moment.

Why *would* you want to create a custom class for your API instead of using an
entity? Two main reasons. First: because the data you're serving *doesn't* come from
the database... or it comes from a mixture of *different* database tables. Or
*second*: the data you're fetching *is* coming from the database... but
because your API looks different enough from your entity, you want to clean things
up by having a class for your API *separate* from your entity class. We'll
play with both cases, starting with the first: when your data comes from somewhere
*other* than a database.

## Creating the Class

Here's the situation: each day, we post a one-of-a-kind *quest* for our dragons
to complete. We want to expose these quests as a new API resource. They'll be able
to list all *past* quests, fetch a *single* quest by the date, or update the *status*
of a quest if they complete it. That's pretty easy. *But* we're *not* going to store
this data in the database. We're going to pretend that the data comes from somewhere
else.

So, instead of making an entity, we're going to
create a brand-new class and put it in this `ApiResource/` directory. This directory
was added *for* us by the API Platform recipe when we originally installed it...
and it's meant to be the home for your API resource classes. Add a new PHP class...
and let's call it `DailyQuest`.

To make this part of your API, just add `#[ApiResource]` above the class.

[[[ code('5a3c9f2cf6') ]]]

That's it! Swing by the docs and... tada! It's *already* in our API
documentation! Though, it *does* look a bit odd: the single `GET` operation
is missing. Normally, we would see something like `/api/daily_quests/{id}`. We'll
uncover the mystery of *why* that's missing in a minute.

## ApiResource Class Directories

Oh, and, by the way: to find all of our API resource classes, API Platform scans
just *two* directories looking for this attribute: `src/Entity/` and `src/ApiResource`.
Though, this *can* be tweaked in `/config/packages/api_platform.yaml` with a mapping
paths config.

Okay, so... how could this *possibly*, *already* be part of our API? It's just a
class. Heck, it doesn't even have any properties! Try the `GET` collection endpoint.
Hit "Execute" and... we get a 404. So... it's not *actually* working. If we try the
`POST` endpoint - we're just sending empty data - it returns a 201 status code as
if it was *successful*... but behind the scenes, absolutely nothing just happened.
No data was created *or* saved.

Look back at our favorite "upgrade" page on the documentation: the one that talks
about providers and processors. If we add the `#[ApiResource]` attribute above
an *entity* class, we get these processors and providers for free. It turns out
that... this is really the *only* difference between adding `#[ApiResource]` above
a random class and adding above an entity. When you use `#[ApiResource]` on an
*entity*, API Platform automatically gives you processors and providers. When you
create a *custom* class, you start with *no* providers and *no* processors. This
means that API Platform has no idea how to *load* data when you make a `GET`
request... nor how to *process* the data at the end of a `POST` or `PATCH` request.

Adding those missing pieces is *our* job! Let's start that *next*.
