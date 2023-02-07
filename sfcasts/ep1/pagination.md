# Pagination & Foundry Fixtures

We're going to start doing more with our API... so it's time to bring this thing
to life with some data fixtures!

For this, I like to use Foundry along with DoctrineFixturesBundle. So, run

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
`DragonTreasure`.

The end result is a new `src/Factory/DragonTreasureFactory.php` file:

[[[ code('eedeb52468') ]]]

This class is just really good at creating `DragonTreasure` objects. It even
has a bunch of nice random data ready to be used!

To make this *even* fancier, I'm going to paste over with some code that I've
dragon-ized. Oh, and we also need a `TREASURE_NAMES` constant... which I'll also
paste on top. You can grab all of this from the code block on this page.

[[[ code('2a817a6662') ]]]

Ok, so this class is done. Step two: to actually *create* some fixtures, open
`src/DataFixtures/AppFixtures.php`. I'll clear out the `load()` method. All we need
is: `DragonTreasureFactory::createMany(40)` to create a healthy trove of 40
treasures:

[[[ code('7c677fd71a') ]]]

Let's try this thing! Back at your terminal, run:

```terminal
symfony console doctrine:fixtures:load
```

Say "yes" and... it looks like it worked! Back on our API docs, refresh...
then let's try the `GET` collection endpoint. Hit execute.

## We have Pagination!

Oh, so cool! Look at all those beautiful treasures! Remember, we added *40*. But if
you look closely... even though the `IDs` don't start at 1, we can see that there
are definitely *less* than 40 here. The response *says* `hydra:totalItems: 40`,
but it only shows 25.

Down here, this `hydra:view` kind of explains why: there's built-in pagination!
Right now we're looking at page 1.. and we can also see the URLs for the last page
and the *next* page.

So yes, API endpoints that return a collection need pagination... just like a
website. And with API Platform, it just works.

To play with this, let's go to `/api/treasures.jsonld`. This is page 1... and then
we can add `?page=2` to see page 2. That's the easiest thing I'll do all day.

## Digging Into API Platform Configuration

Now if you need to, you can *change* a bunch of pagination options. Let's see
if we can tweak the number of items per page from 25 to 10.

To start digging into the config, open up your terminal and run:

```terminal
php bin/console debug:config api_platform
```

There are a lot of things that you can configure on API Platform. And this
command shows us the *current* configuration. So for example,
you can add a `title` and `description` to your API. This becomes part of the
OpenAPI Spec... and so it shows up on your documentation.

If you search for `pagination` - we don't want the one under `graphql`... we
want the one under `collection` - we can see several pagination-related options.
But, again, this is showing us the *current* configuration... it doesn't necessarily
show us *all* possible keys.

To see *that*, instead of `debug:config`, run:

```terminal
php bin/console config:dump api_platform
```

`debug:config` shows you the current configuration. `config:dump` shows you a full
tree of *possible* configuration. Now... we see `pagination_items_per_page`.
That sounds like what we want!

This is actually *really* cool. All of these options live under something called
`defaults`. And these are snake-case versions of the *exact* same options that you'll
find inside the `ApiResource` attribute. Setting any of these `defaults` in the config
causes that to be the default value passed to that option for *every* `ApiResource`
in your system. Pretty cool.

So, if we wanted to change the items per page *globally*, we could do it
with this config. *Or*, if we want to change it *only* for one resource, we can
do it above the class.

## Customizing Max Items Per Page

Find the `ApiResource` attribute and add `paginationItemsPerPage` set to 10:

[[[ code('f13c0bab76') ]]]

Again, you can see that the options we already have... are included in the `defaults`
config.

Move over and head back to page 1. And... voilà! A much shorter
list. Also, there are now 4 pages of treasure instead of 2.

Oh, and FYI: you can also make it so that the *user* of your API can determine how
many items to show per page via a query parameter. Check the documentation for how
to do that.

Ok, now that we have a lot of data, let's add the ability for our Dragon API users
to search and filter through the treasures. Like maybe a dragon is searching for a
a treasure of individually wrapped candies among all this loot. That's next.
