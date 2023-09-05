# Custom Resource Provider Part1

We have our new API resource class and, for the most part, it works like normal. We can customize things, like... instead of `DailyQuests`, maybe we change the `shortName` to just `Quest`. If we look over here, as expected, the title has changed, along with all the URLs.

Now, to be able to load data and have this collection endpoint *not* return a 404, we need a *state provider*. And it's not *just* the `GET` endpoint. The `PUT` endpoint uses the state provider, as well as the `DELETE` and `PATCH` endpoints. All three of those will need to call the state provider to load the entity they're editing or deleting before *actually* editing or deleting it. So let's make one! 

We've done this before, but this time will be a *little* different since we're not going to pair it with an entity. Run

```terminal
./bin/console make:state-provider
```

and let's call it

```terminal
DailyQuestStateProvider
```

*Awesome* name. Now we'll spin back over, open the `/State` directory and... there it is! We've seen this before. Our job is to just return the daily quests for the current page. We'll start very simply, by returning an array with two hard-coded `new DailyQuest()` objects. They're both empty at the moment because that class doesn't have any properties on it yet. We also know that when you create a provider, we actually need to *use* it, since it doesn't happen automatically. We need to tell API Platform that this is supposed to be the provider for our API for our `DailyQuest` class. To do that, say `provider`, and then say `DailyQuestStatsProvider::class`. That's it!

Let's try it out. Head back over and "Execute" the collection endpoint. And... *yes*! No more 404! Now we have a 200, and we can see that it has two items left. Here they are. All we have right now are the JSON-LD fields `@id` and `@type`, and that makes sense, since our class doesn't have any other properties on it. *But*, at this point, I want to go back and talk about why the `GET` *one* endpoint is missing. We *have* the `GET` *collection* endpoint, but we're missing the `GET`-a-single-item endpoint. Why is that? That's because every API resource needs what's called an "identifier". Right now, our class *doesn't* have an identifier, and that actually causes the two routes to collide. Let me show you!

Spin over and run:

```terminal
./bin/console debug:router
```

One of the cool things about API Platform is that, for every operation, it also dumps a *route*. I'll make this a little smaller... There we go. You can see all of our different routes here for our quests. And check this out! Here's the one for `_get_collection` and, above it, we see the one for `_get_single`, which has the *same* URL as `_get_collection`. *Why*? That's because it doesn't know if our API resource has a property called "id" that's its identifier, or "foo bar", or "date", so it just generates it with *nothing*. And because these have the same URL, the single `GET` doesn't show up in the documentation.

So... how do we fix it? The easiest way is to add an `$id` property. Back over here, say `public int $id` and, for simplicity, we'll also add a constructor here where we can pass the `int $id`. And then we'll just pop this in here. Over in `DailyQuestStateProvider.php`, we'll just make up a couple of IDs right now... so `4` and `5`. As soon as we do this... if we run

```terminal
./bin/console debug:router
```
again... check it out! The single `_get` has a *different* URL from the `_get_collection`. And look! The `id` was also missing from `put`, `patch`, and `delete`, and now it's there. Over here, if we refresh... we see the *same* thing. So this `id` identifier is really important because it's going to be used in the URL. It will also be used when it generates the `@id` field for each item. Here you can see that `@id` is now pointing to `/api/quest/4`. It recognizes that the `id` is what's used to complete that URL. *How* did I know that our that our property `id` is the identifier? I'm... *honestly*... not entirely sure. But it seems that the name `id` is a special name *somewhere* in API platform. And if you use `id`, then API Platform says:

`Oh, that must be your identifier!`

*But* there's a more explicit way to say that a property is an identifier, and we'll see that in a second. Because, in our case, we don't want an integer `id` as the identifier. We're creating a new quest every single day, so we want the identifier to be the date. In that case, we'd say something like `/api/quest/2023-06-05`.

Check this out! Over here, instead of `public int $id`, we're going to say `public \DateTimeInterface $day`, and do the same thing down here - replace the argument with `\DateTimeInterface $day` and `$this->day = $day`. Then, in our `DailyQuestStateProvider`, we'll say... how about `new \DateTime('now')` and `new \DateTime('yesterday')`. As we do that, if we refresh... you'll notice that we're back to where we were before. We're missing the ID on `PUT`, `DELETE`, and `PATCH`, and our single `GET` is *gone*. That's because it doesn't know that this `$day` is meant to be our identifier. When we try the `GET` collection endpoint, other than that problem... hey! The `day` actually shows up inside of our output like a normal property.

What we want to do is tell API Platform:

`Hey! This isn't a normal property! This "day" is our identifier.`

The way we do that is by adding an `#[ApiProperty]` above this, followed by `identifier: true`. *But*, that's going to cause its own set of problems. We can see that this *does*, in fact, fix all of our routes. Everything seems to look good. But when we try the collection endpoint... we get a 400 error: `Unable to generate an IRI for the item of type \"App\\ApiResource\\DailyQuest\"`. So this *loaded* our two `DailyQuest` objects, and when it tried to generate the `@id` property (which is the IRI), for some reason, it encountered a problem. To see what that is, go down to the web debug toolbar and open up that request in the profiler.

On the exception tab, we can see that there were two exceptions on this page. This is a *nested* exception. We have this top level - `Unable to generate an IRI` - but doesn't really tell us *why* there was a problem generating an IRI. Down here, we can see that `We were not able to resolve the identifier matching parameter "day"`. This error isn't *super* clear either, so I'll help fill in the blanks. What this is basically saying is, when it tried to generate the URL, it couldn't because, to create this IRI string, it tried to transform our `DateTimeInterface` object into a string, and since you can't convert `DateTimeInterface` objects into strings, it threw that error. We've actually chosen a pretty tricky IRI to work with here, which is kind of cool. There *is* an internal system where you can actually help convert this to a string and back. But another way to do this, which I prefer, is to just create a *property* for our identifier. We're going to create a new function called `getDayString()` which will return a `string`. Then, we'll `return $this->day->format()`, and the format we want is `y-m-d`.

To make *this* the identifier, we're actually going to move the API property down here. Perfect! Back over here... our routes still look correct. You can see we have "dayString" now, and when we try our `GET` collection endpoint... check that out! We see `"@id": "/api/quest/` and then the date. That's exactly what we wanted! *Though*, now we have a `dayString` field, as well as the `day` itself, which we didn't really want. We just wanted that to be used as the identifier. That's part of the URL, and we'll talk more about this later. But effectively, this is a case where we'll want to hide a couple of fields in our DTO.

Above the `\DateTimeInterface`, we can hide a property *entirely* from our API by using an `#[Ignore]` attribute from Symphony serializer. If we head over here and "Execute" that... boom! That field is completely gone. It can't be read and it can't be written. We *could* do the same thing for the `dayString`, but *another* option is to say `readable: false`. This means it won't be *readable*, but it will still *technically* be writable, though there's no set `dayString`. When we "Execute" this, that field has disappeared too.

*This* is the setup we want! We have the ID we want, we don't have any extra fields that we *don't* want, and we can actually *add* the other fields that we *do* want. To do *that*, we're going to create an Enum first. You'll see why in a second. Create an `/Enum` directory... and, inside there, create a new PHP class called `DailyQuestStatusEnum`. I'll actually paste some code here really quick. Perfect. This is just a way for us to keep track of the *status* of this daily quest. Then, in `DailyQuest`, we're going to use that on our property here. We'll say `public string $questName`, `public string $description`, and whatever other properties we need in our API, like `public int $difficultyLevel`, and a `public DailyQuestStatusEnum` called `$status`. Nice!

If you head over here and hit "Execute", you'll see that we don't actually see any of these new fields yet. That's because they're not actually populated. If we refresh the page and go down to the documentation, you would see that it *does* actually show these as part of the API, so if they were populated, they *would* be returned. If we head over to `DailyQuestStateProvider.php`... this is where we're going to return them. Say `return $this->createQuests()`. We'll put this into a little private function, and then *for* that private function, we'll just paste that in. You can get that from the code block on this page.

This isn't *particularly* interesting. In this case, we're going to create *50* quests, we have a little `for` loop, we're creating the `DailyQuest`, every quest is for one day further back in the past, and then just some random data after that. Some of these quests are `ACTIVE`, and some of them will be `COMPLETED`. And notice that we're using `getDayString()` as the key for this array. We don't actually *need* to do that, but it's going to be handy in a moment. That's actually *ignored*. The only thing API Platform cares about is that we return an iterable - some sort of *collection* of `DailyQuest` objects. It doesn't actually care what the *key* is inside of there. If we head over here and hit "Execute" again... look at that! We have 50 items, and we have data on *all* of them. That's *beautiful*.

Next: Let's get our provider working for the item operations (meaning when we fetch a *single* item, which happens for this operation), as well as `PUT`, `DELETE`, and `PATCH`.
