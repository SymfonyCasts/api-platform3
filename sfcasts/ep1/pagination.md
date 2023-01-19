# Pagination & Foundry Fixtures

We're going to start doing more with our API. So let's make this API come alive
with some data fixtures!

For fixtures, I like to use Foundry along with DoctrineFixturesBundle. So, run

```terminal
composer require foundry orm-fixtures --dev
```

to install both as `dev` dependencies. Once that finishes, run

```terminal
php bin/console make:factory
```

## Adding the Foundry Factory

If you haven't used Foundry before, for each entity, you create a *factory* class
that's really good at *creating* that entity. I'll hit zero to generate the one for
`DragonTreasure` entity.

The end result is that we now have a `src/Factory/DragonTreasureFactory.php` file.
This class is just really good at creating dummy `DragonTreasure` objects. It even
has a bunch of nice random data ready to be used.

To make this *even* nicer, I'm going to paste over that with some code that I've
dragon-ized. Oh, and we also need a `TREASURE_NAMES` constant... which I'll also
paste in at the top. You can grab all of this from the code block on this page.

Ok, so this class is done. Step two: to actually *create* some fixtures, open
`src/DataFixtures/AppFixtures.php`. I'll clear out the `load` method. All we need
is: `DragonTreasureFactory::createMany(40)` to create 40 of these. That's a true
treasure *trove*.

Let's try this thing! Back at your terminal, run:

```terminal
php bin/console doctrine:fixtures:load
```

Say "yes" and... it looks like that worked! Back on our API docs, refresh...
then let's try the `GET` collection endpoint. Hit execute.

## We have Pagination!

Oh, so cool! Look at all those beautiful treasures! Remember we added *40*. But if
you look closely... even though the `IDs` don't start at 1, we can see that there
are definitely *less* than 40 here. The response *says* `hydra:totalItems: 40`,
but it only shows 25.

And, down here, this `hydra:view` kind of explains why: there's built-in pagination!
Right now we're looking at page 1.. and we can also see the URLs for the last page
and the *next* page.

So yes, API endpoints that return a collection need pagination. And with API Platform,
it just works.

To play with this, let's go to `/api/treasures.json`. This is page 1... and then
we can add `?page=2` to see page 2. That's the easiest thing I'll do all day.

## Digging Into API Platform Configuration

Now if you need to, you can *change* a bunch of pagination options. Let's see
if we can change the number of items per page from 25 to 10.

To start digging into the config, open up your terminal and run:

```terminal
php bin/console debug:config api_platform
```

In general, there's a lot of things that you can configure on API Platform. And this
command shows us the *current* configuration for API Platform. So for example,
you can add a `title` and `description` to your API. This becomes part of the
OpenAPI Spec... and so it shows up on your documentation. And there is a *lot* of
other possibilities hiding in here.

If you search for `pagination` - we don't want the one under `graphql`... we
want the one under `collection` - we can see several pagination-related options.
But, again, this is showing us the *current* configuration... it doesn't necessarily
show us *all* possible keys.

To see *that*, instead of `debug:config`, run:

```terminal
php bin/console config:dump apl_platform
```

`debug:config` shows you the current configuration. `config:dump` shows you a full
tree of *possible* configuration. Now... we see `pagination_max_items_per_page`.
That sounds like what we want!

This is actually *really* cool. All of these options are under something called
`defaults`. And these are snake-case versions of the *exact* same options that you'll
find inside the `ApiResource` attribute. Setting any of these `defaults` in the config
causes that to be the default value passed to that option for *every* `ApiResource`
in your system. Pretty cool.

So, if we wanted to change the max items per page *globally*, we could do it
with this config. *or*, if we want to change it *only* for one class, we can
do it above the class.

## Customizing Max Items Per Page

Find the `ApiResource` attribute and add `paginationItemsPerPage` set to 10.

Again, you can see that the options we already have... are included in the `defaults`
config.

Before we try this over here, go back to page 1 and... and voilà! A much shorter
list. And now you can see that there are 4 pages of treasure instead of 2.

Oh, and FYI: you can also make it so that the *user* of your API can determine how
many items are shown per page. Check the documentation for how to do that.

Ok, now that we have a lot of data, let's add the ability for our Dragon API users
to search and filter through the treasures. Like maybe a dragon is searching for a
a treasure of individually wrapped candies below all the gold. That's next.
