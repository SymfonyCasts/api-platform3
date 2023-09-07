# Pagination on a Custom Resource

When we fetch the collection of quests, we see all *50* of them! There's no
pagination... a fact I can prove because, at the bottom we don't see any extra data
about the pagination.

Usually... if we peek at the treasures collection... at the bottom of the response,
API Platform adds a `hydra:view` field that describes how you can paginate through
these resources. But over here for quests... nothing done here!

## Pagination Comes from the Provider

But where *does* pagination come from in API Platform? It turns out that pagination
is completely the responsibility of your provider. It's... pretty simple actually.
*Whatever* your collection provider returns - whether it's just an array of quests...
or some sort of iterable of quests - is what is serialized to JSON. *But*, if
it returns an iterable object that happens to implement a special `PaginatorInterface`,
API Platform will see that and render the `hydra:view` pagination details.

## Using The TraversablePaginator

So, if we want our collection to support pagination, step one is, instead of
returning this array, to return an object that implements that interface. And,
fortunately, API Platform already has a class that can help us!

First, set the array to a `$quests` variable. Then return new `TraversablePaginator`
from API Platform. This takes a few arguments. First, a traversable - basically
the results that should be shown for the *current* page. Right now, we'll still
use *all* 50 quests. Oh, except this needs to be an *iterable*... so wrap them
in a new `ArrayIterator`.

Next is the current page - hardcode that to 1 for now - then items per page - hardcode
that to 10 - and finally the total number of items, which for now, I'm just going
to count `$quests`.

This is *not* a very smart paginator yet: it will always be on page 1 and will
show ever result. But when we go over, refresh... and scroll to the bottom, we *do*
see the pagination info! According to this, there are 5 pages of results... which
makes sense: 10 items per page and 50 total items. But you'll also see that we're
*still* returning 50 items. There's not *real* pagination happening!

Why? Because it's up to *us* to figure out which page we're on and to pass only
the *correct* results to the paginator. If we pass it 50 items, it'll render 50
items, regardless of what we tell it are the max per page.

## Organizing our Variables

To help us do that, let's set a few variables: `$currentPage` hardcoded to 1,
`$itemsPerPage` hardcoded to 10 and `$totalItems`. For this, call a new private method
`countTotalQuests()`. I'll hit Alt+Enter and  add that method at the bottom.
This will return an `int`... and I'm just going to return 50... because that's
the *total* possible quests we have in our "fake" database. If you were using a
database, you'd count every available row. Let's change the code in `createQuests()`
to use this.

That probably looked a bit silly: why am I creating a private method to return
something so simple? Well, what I'm really trying to show highlight are the two
distinct "jobs" we have for pagination. First, to return the correct subset of
our 50 results - which we'll do in a moment. Second, to return the count of the
*total* number of items. When you use Doctrine, it executes 2 separate queries
for this: one to fetch the result with a LIMIT and OFFSEt, and a second COUNT
query to count *all* of them.

## Current Page, Limit, Offset: The Pagination Service

Ok, back on top, let's use these variables: `$currentPage`, `$itemsPerPage` and
`$totalItems`.

Ok cool... but what we *really* need to do is determine the *actual* current
page and then use that to return only a *subset* of the results. Like, if we're
showing 10 per page and we're on page 2, we should only return quests 11 through
20.

Pagination works via a `?page` query parameter: `?page=2` should mean we're on
page 2. But our code isn't reading this yet. Look: it still thinks we're on page 1...
because we've hardcoded that. To get the correct page, we *could* try to read the
query parameter directly... but we don't need to! API Platform gives us a service
that already holds *all* the pagination info.

On top, add a second constructor argument called `private Pagination` - from API
platform `$pagination`. Below, set `$currentPage` to
`$this->pagination->getPage()`, which needs the `$context` that we have as an
argument on this method. Then `$itemsPerPage` set to
`$this->pagination->getLimit()` passing `$operation` and `$context`. We can also
get an `$offset` in a similar way, which is *super* handy. If we're on page 2
and the limit is 10, the `Pagination` service will calculate that the offset
should be 11. Below, dump all four variables.

Let's check this out! Go back to page 1, refresh and look at that! Page 1, 30 items
per page, the limit and offset 0. If we go to `page=2`, then it's page 2, the number
per page is still 30 and the offset is 30.

Now, where is it getting 30 as the items per page? Well, that's the default in API
Platform for any resource. But this is something you can configure on your
`#[ApiResource]` attribute: `paginationItemsPerPage` set to, how about, 10.

Now try it. That changes the 10 and the offset is 10. If we go to page
3, our per page is still 10. And now it's saying:

> Hey, since we're on page 3, you should start at result 20.

We're in *great* shape now. Our *final* job is to use this info to return the
correct *subset* of results, instead of *all* of the quests. To do that, I'm going
to pass `$offset` and `$itemsPerPage` and to `createQuests()`.

Down here, add `int $offset` and `int $limit` with a default of 50. And use those:
`$i = $offset` and then `$i <=` `$offset` plus `$limit`.

Ok team check it out! We're on page 3 and... these are the items from page 3!
It's more obvious if we go to page 1. See the descriptions: description 1, 2, 3
and so on. So, pagination is working on our collection!

Though, in this simple example, I need to make sure I don't break the item provider.
Because we're looking up the day string as an array key, we need to return *all*
the quests. To make sure that happens, pass 0 and 50.

In a real app, you would make this smarter by, for example, querying for the *one*
item you need... instead of loading *all* of them.

So that's pagination for a custom resource. What about filtering? We're going to
talk about creating custom filters in a future tutorial. But spoiler alert: the
filtering logic is *also* something that happens right here inside of the collection
provider.

Next: let's remove all of the API resource stuff from our `User` entity and add it
to a new class that's going to be dedicated to our API. Woh.
