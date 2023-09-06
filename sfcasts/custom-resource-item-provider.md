# Custom Resource Item Provider

Let's try to get a *single* item here. I'll change the date, hit "Execute", and... *200 status code*. Except this is returning a *collection* - the *exact* same data as our *collection endpoint*. Earlier, we talked about how each *operation* can have a provider. Right now, when we put `provider` under the `#[ApiResource()]`, *this* becomes the provider for every operation. That's *fine*, but it's important to note that *some* operations are fetching a *collection* of resources and *some* are fetching a single item. Inside of our provider, the `operation` will help us see the difference. We'll say `dd($operation)`... and then, over here, copy this URL, paste it in a new tab and add `.jsonld` at the end of it. There we go! In this case, you can see that we're getting the `Get` operation. If we did the collection operation, it's `GetCollection`, which we saw earlier. So we can leverage that to spot the difference between the two.

Back over in our provider, say `if ($operation instanceof CollectionOperationInterface)` so all collection operations will implement that, and then we're going to `return $this->createQuests()`. Down here, we need the item operation. We're trying to find the *one* operation that we're currently using. You can see that this fixes the *collection* operation, but now we need a way to get this from the URL so we can fetch the quest matching that date. To do that, we're going to `dd($uriVariables)`. When we refresh... we have a `dayString` inside!

Notice that, in `DailyQuest.php`, we're not saying what our URL should be. You *can* do that, but by default, API Platform *automatically* figures out what the route and URL should look like. When we run

```terminal
./bin/console debug:router
```

by default, it says `/api/quests/` and, since our identifier is `dayString`, it adds `{dayString}` inside of the route. That's cool because *that* becomes our one URI variable. And when we have our provider, it's going to pass us any of the URI variables that were matched in the route, like `dayString`. That makes us *dangerous*.

Right now, we know our provider down here is acting as the item provider. *Our* job is to return a *single* daily quest or *null*. Say `$quests = $this->createQuests()`, and then we'll `return $quests[$uriVariables['dayString']] ?? null`. And remember, this works because we made the key `dayString` for *all* of our quests so we could use that to fetch them. In a *real* app, we would want to do this more efficiently, since it doesn't really make sense for us to load *all* of our quests just to return *one*, but for our test app, this will work fine. If we try this... *got it*! It returns *one*. And if we try to use a random date that *doesn't* exist in our data, like "2013"... we get a 404. API Platform sees that we returned null and it handled doing the 404 for us.

Okay! We now have a fully functional state provider! We'll talk about this more later, including things like pagination. Next: Let's shift our focus and create a *state processor*.
