# Custom Resource State Processor

We haven't configured the `operations` key on our `#[ApiResource]`. And so, we
get *every* default operation. But really, we only need a few of them. Add
`operations` with a `new GetCollection()`, `new Get()` so we can fetch a single
quest one, and `new Patch()` so users can update the status of an *existing* quest
when they complete it.

When we refresh now... I love it!

Speaking of that `Patch` operation, when it's used, API Platform will call the
state processor so we can save... or do whatever we want. We don't have one yet,
so that'll be our next job.

## Adding a Patch Test

But let's start with a test. Down in `tests/Functional/`, create a new class
called `DailyQuestResourceTest`. Make this extend the `ApiTestCase` that we created
in the last tutorial and `use ResetDatabase` from Foundry to make sure our database
is *empty* at the start of every test. Also `use Factories`.

Ok, we don't *need* these... since we're not going to talk to the database, but if
we decide to do later on, we're *ready*.

Down here, add `public function testPatchCanUpdateStatus()`. The first thing we need
to do here is create a `new \DateTime()` that represents `$yesterday`: `-1 day`.

Remember: in our provider, we're creating daily quests for today through the last
50 days. When we make a `PATCH` request, our item provider is called to "load" the
object. So we need to use a date that we know will be found.

Now say `$this->browser()`, `->patch()`... and the URL:
`'/api/quests/'` with `$yesterday->format('Y-m-d')`. Pass a *second* options argument
with `json` and an array with `'status' => 'completed'`.

The `status` field is an enum... but because it's backed by a string, the serializer
will deserialize it from the string `active` or `completed`. Finish with
`->assertStatus(200)`, `->dump()` (that will be handy in a second), and then
`->assertJsonMatches()` where we'll check that `status` changed to `completed`.

I love it! We're not really going to *save* the updated status... but we should at
least see that the final JSON has `status` `completed`. Copy this test name... and
over here, run: `symfony php bin/phpunit --filter=` and paste that name:

```terminal-silent
symfony php bin/phpunit --filter=testPatchCanUpdateStatus
```

And... whoops! We get a 415. It looks like I forgot something. The error says:

> The content-type `application/json` is not supported.

Ah... I forgot to add a header to my `PATCH` request. Add `headers` set to an
array with `Content-Type`, `application/merge-patch+json`.

We talked about this in the last tutorial: this tells the system what *type* of patch
we have. This is the only one that's supported right now, but it's still required.

If we try that now... it *passes*! But wait, I think I tricked myself! Comment-out
the `status` and then the test... still passes? Yup, change that to `-2 days`...
and `$yesterday` to just `$day`.

In our provider, we make every other quest active or complete: and yesterday
*starts* as complete. Whoops! When we try the test now... it fails. Add the
`status` back to the JSON and now... got it! The test passes!

Behind the scenes, here's the process. One: API Platform calls our provider to
fetch the *one* `DailyQuest` for this date. Two: the serializer updates that
`DailyQuest` using the JSON sent on the request. Three: the state process is called.
And four: the `DailyQuest` is serialized into JSON.

## Creating the State Processor

Except... in our case, there is no step three... because we haven't created a
state processor yet! Let's add one!

```terminal
php bin/console, make:state-processor
```

and call it `DailyQuestStateProcessor`.

Another *brilliant* name!. Go check it out: it's empty and *full* of potential.
In `DailyQuest`, the processor should be used for the `Patch` operation, so add
`processor: DailyQuestStatsProcessor::class`.

To prove that this is working, `dd($data)`.

Okay! Try the test again:

```terminal-silent
symfony php bin/phpunit --filter=testPatchCanUpdateStatus
```

And... boom! The `status` is set to `completed`. *So*, because this is a `PATCH`

By the way, we added the `processor` option directly to the `Patch()` operation,
but we can *also* put it down here on the `#[ApiResource()]` attribute directly.
That makes no difference... because this is the only operation we have that even
*uses* a processor: GET method operations will *never* call a processor.

## State Processor Logic

Anyway, this is *normally* where we would save the data or... do *something*,
like send an email if this were a "reset password" API resource.

To make things a *bit* realistic, let's add a `$lastUpdated` property
`DailyQuest` object and update it here. Add
`public \DateTimeInterface $lastUpdated`.

Then populate that inside the state provider:
`$quest->lastUpdated` equals `new \DateTimeImmutable()`... and let's add some
randomness here to be between 10 and 100 days ago.

Finally, head over to the state processor. We know that this
is only used for `DailyQuest` objects... so `$data` *will* be one of those. Help
your editor with `assert($data instanceof DailyQuest)` and, below,
`$data->lastUpdated = new \DateTimeImmutable('now')`.

Cool! We don't have a test assertion for that field, but we *are* still dumping the
response... and we can see it here. I'm looking at my watch and... that *is* the
correct time in my little corner of the world. Our state processor is liave!

Celebrate by going back to the test and removing that dump.

Next: Let's make our resource more interesting by adding a relation to *another* API
resource: a relation to *dragon treasure*.
