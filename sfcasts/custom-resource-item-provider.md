# Custom Resource Item Provider

Let's try to get a *single* item. I'll change the date, hit "Execute", and...
*200 status code*. Hold your horses... this is returning a *collection*: the
*exact* same data as our *collection endpoint*!

## Collection vs Item Operations

Ok, each *operation* can have its own provider. But when we put `provider` direclty
under `#[ApiResource]`, *this* becomes the provider for *every* operation. That's
peachy... given you don't forget that *some* operations fetch a *collection*
of resources while other fetch a *single* item.

Inside our provider, the `$operation` helps us know the difference. `dd()` that...
then, over here, copy the URL, paste it in a new tab and add `.jsonld` to the end.
There we go! This is a `Get` operation. If we try to fetch the collection, it's
`GetCollection`.

Back in the provider, `if ($operation instanceof CollectionOperationInterface)`,
`return $this->createQuests()`.

Below, we know this is an "item" operation.

## URI Variables

So this *does* keep the *collection* operation working. Now, we need a way to extract the date
string from the URL so we can find the *one* quest that matches. How can we get
that? `dd($uriVariables)`.

When we refresh... behold: there's a `dayString` inside! Notice that, in `DailyQuest`,
we never configure what the URL should look like. You *can* do that, but by default,
API Platform *automatically* figures out what the route and URL should look like.
Run:

```terminal
php bin/console debug:router
```

For the item endpoints, it's `/api/quests/{dayString}`: the `dayString` is a
wildcard in the route. In the provider, `$uriVariables` will contain *every*
variable part of the URI - so `dayString` in our case. That makes us *dangerous*.

## Returning a Single Items

Down here, we need to return a *single* `DailyQuest` or *null*. Say
`$quests = $this->createQuests()`, then
`return $quests[$uriVariables['dayString']]` or `null` if it's not set.

Remember: this works because the array uses `dayString` for each key.
In a *real* app, we would want to do this more efficiently:
it doesn't make sense to load *every* quest... just to return *one*. But for our
test app, this will work fine.

Ok, try that endpoint. Got it! One result. And if we try a random date that
*doesn't* exist... like "2013"... we get a 404. API Platform sees that we returned
`null` and it handled the 404 for us.

We are now the proud parents of a fully functional state provider! Though
we'll talk about this more soon - including topics like pagination. But next: let's
shift our focus to creating a *state processor* for our custom resource.
