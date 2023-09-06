# Custom Resource Processor

We haven't specified the operations key on our API resource, so right now, we're getting *every possible operation* on our quest. We only need a few of them, so we're going to *specify* the operations. The ones we're looking for are `new getCollection()`, `new Get()` so we can fetch just a single one, and `new Patch()` so users can update the status of an *existing* quest when they complete it. When we refresh... perfect! We're *just* getting the three we want.

*Now*, let's shift gears and talk about the state processor. That will be called when we make a `PATCH` request. We want users to be able to make a `PATCH` request so they can change the *status* of a quest when they've completed it. Let's start by creating a new test. Down here, in `/tests/Functional`, we'll create a new test class called `DailyQuestResourceTest`. We'll make this extend the `ApiTestCase` that we created last time, and then we'll `use ResetDatabase` from Foundry to make sure our database is *empty* at the start of every test, as well as `use Factories`. *Technically*, we don't *need* `use ResetDatabase` since we're not *talking* to the database right now, but if we decide to do that later on, we're *ready*.

Down here, say `public function testPatchCanUpdateStatus()`. The first thing we need to do here is create a `new \dateTime()` object that represents `$yesterday`, `-1 d` or "minus one day". That's because, if you'll recall, in our provider, we're creating daily quests for today through the last 50 days. Since we're making a `PATCH` request, our provider will be called to load that *first*, and then our new JSON will be added. What we need to do is make a request to a date that's going to match one of the quests in our provider. Let's try it!

Say `$this->browser()`, followed by `->patch()`... and the URL is going to be `'/api/quests/'` with `.$yesterday->format('Y-m-d')`. Perfect! Then we'll pass a *second* argument, which will be the *options* for this, using `json` with `'status' => 'completed'`. This comes from the `/Enum`. We have an Enum for `status`, but behind the scenes, it's either `active` or `completed`. We're passing `completed`. Down here, say `->assertStatus(200)`, `->dump()` (that will be handy in a second), and then `->assertJsonMatches()` where we'll update the `status` to `completed`. *Awesome*.

In reality, we won't actually *save* this updated status anywhere, but we should at least see that the final JSON has `status` `completed`. Copy this test name... and over here, run

```terminal
symphony php bin/phpunit --filter=
```

and paste that name. And... whoops! We got a 415. It looks like I forgot something. The error says:

`The content-type \"application/json\" is not
supported.`

Ah... I forgot to add a header to my `PATCH` request. Say `'headers' => ['Content-Type']` and we'll set that to `application/merge-patch+json`. We talked about this in the last tutorial. This tells the system what *type* of patch we have, and it's the only one that's supported right now. If we try that now... it *passes*. So basically, API platform is loading this daily quest from our state provider, deserializes this JSON onto it, and then *reserializes* that object into JSON. There's *no* state processor working behind the scenes here.

*But* I'm going to comment out that status really quick. Ah... it *still* says it works. I'll change this to `-2 d`... and `$yesterday` to just `$day`. Remember, in our provider, we're making things active or inactive at random. It looks like I've selected one that is "completed" by default. If we try this again... we now have one that's in an "active" state by default, so if we set the `status` to `completed` now... there we go! We can see it. So again, there's no state processor happening, but it *does* load our daily quest, *deserializes* the JSON onto it (so it updates the `$status` property of our daily quest), and then it *reserializes* it at the bottom.

Now we want this to actually *do* something, so we're going to create a state processor, and we *already* know how to do this. Run

```terminal
./bin/console, make:state-processor
```

and we'll call it

```terminal
DailyQuestStateProcessor
```

(another *brilliant* name). And...perfect! We can see it here, and it's currently *empty*. The last thing we need to do is hook it up. We want this to happen for the `PATCH` request so, right here, we'll say `processor: DailyQuestStatsProcessor::class`. And to prove that this is working, we can `dd($data)`.

Okay! Let's try the test again, and... got it! We can see that the status is set to "completed". *So*, because this is a `PATCH` request, it hits our state provider to *load* the daily quest that matches, the *serializer* updates the object with this JSON, and *then* it calls our state processor. By the way, we put the processor on the `Patch()` operation, but we can *also* put this down here on the `#[ApiResource()]` class. That makes no difference at all because this is the only operation we have that even *uses* a processor. We don't call a processor for a `Get` or `getCollection`. If we *did* have a `Delete()` operation however, then having the processor down here would mean that this is the processor *also* used for that operation. In that case, you might need to do something a little different in your processor based on the operation, which isn't a problem because we passed the operation as an argument. We'll actually see that later when we create a processor that handles both deletes and database saves when we go back to a custom resource for our entity.

Anyway, this is *normally* where we would save this data or do something with it, rather than have a state processor. We don't really have a database, but we can *at least* add a `$lastUpdated` property to our daily quest object so we can see the difference. Over here, we're going to create a new `public \DateTimeInterface` with `$lastUpdated`. This will be a new property on our API. Then we'll make sure that it's populated inside of our state provider, so it's there when we fetch data, by saying `$quest->lastUpdated = new \DateTimeImmutable()`. And let's add some randomness here -  `sprintf('- %d days')` - and we'll have that be something random, between `10` and `100`. Cool!

Now let's head over to our state processor. We know that this `DailyQuestStateProcessor` is only being used for daily quests, so this `$data` will be a daily quest object. To help our editor, say `assert($data instanceof DailyQuest)` and, below, `$data->lastUpdated = new \DateTimeImmutable('now')`.

Okay, now watch *this*. When we run the test, we're not doing an assertion for that, but we *are* still dumping the output, and we can see here. I'm looking at my watch and... that *is* the correct time for my little corner of the world, so it *did* hit our state processor. Awesome! Now we can go back to the test, and since this is working, we can remove this `dd()`.

Next: Let's make our resource more interesting by adding a relation to *another* API resource - a relation to *dragon treasure*.
