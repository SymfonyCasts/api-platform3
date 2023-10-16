# Publish

Let's run *all* of those `DragonTreasure` tests again. And... we have *three* failures. *One* of them is coming from `TestAdminCanPatchToEditTreasure` on line 200. The test isn't super important, but the *failure* is. Line 200 says `->assertJsonMatched('isPublished', true)`.Right now, we don't have `isPublished` in our `DragonTreasureApi` *at all*.

This is a *tricky* field. Previously, this field was readable *only* by admins or the owner - *not* by other users. So let's add that back and keep it exactly the same. Say `public bool $isPublished = false`, and then we need to get into the mapper. Down here, we can get rid of this `TODO` and say `$entity->setIsPublished($dto->isPublished)`. So if we *change* published, we will sync that back to the entity. On the other side... it doesn't matter where... say `$dto->isPublished = $entity->getIsPublished()`. *Cool*.

We don't have any security on that. It's just a normal field. So when we run the tests... we have *a couple* that pass, but the *original* test *fails* - `testGetCollectionOfTreasures` - because it's not expecting the `isPublished` to be there. Let me show you! Over here is the *first* test, and at the bottom, we've stated that these are the *exact* properties that we should have if we are just fetching treasures as an anonymous user. Since we're *not* the owner, we shouldn't see the `isPublished`. So now we need to figure out how we can *show* this `isPublished` *only* if we're the owner of the treasure *or* an admin.

A moment ago, we were looking at `DragonTreasureApiVoter.php`. When we call this with the `EDIT` attribute, it checks to see if we are an admin, and if we *are*, it grants us access. It *also* checks to see if we're the *owner*, and if *that's* true, it will *also* grant us access. So we definitely want to include that field in the API if this voter passes. To do that, we're going to leverage *security*.

Above this property, say `#[ApiProperty(security: 'is_granted("EDIT", object)')]`. If you wanted to, you could change this attribute to something else (maybe like `owner`, if that's more clear). This `EDIT` sounds a little funny here, since we're just deciding if we should *include* this field in the response, but when we go over and run the tests... this *fixed* our first test. It *passed*, and the `isPublished` field is no longer being shown in that case. *But*, curiously, we made *another* test *fail*. This time, it's `testPublishTreasure`, and the issue is coming from line 244.

Let's pop over to that test and search for it. Okay, as the name suggests, we're testing to see if we can publish this treasure. Here, we're creating a treasure that is `'isPublished' => false`, logging in as its owner, and then we send a nice `patch()` request to set `isPublished` to `true`. Down here, we assert that the JSON matches, and *this* is the line that's failing. It took me a little bit of debugging to figure out what's going on here. The issue, in this case, is that when the JSON is deserialized, `isPublished` isn't actually writable, so it calls our `security` expression to see if it should be *allowed* to write this `isPublished` field. And *that's* what's failing. This *might* be a bug, and I currently have an issue open on API Platform that talks about this very thing. Even though we're making a `patch()` request (so there's an existing treasure), when this expression is called during deserialization, `object` is *always* null. And since `object` is always null, it goes into our voter. Our *voter* only supports it if `object` is a `DragonTreasureApi`, so this is returning `false`. *No* voters support this, and when that happens, access is *denied*. So it *looks like* `isPublished` should *not* be writable.

The workaround for this is a little weird, but stay with me here. We're basically going to say

`allow access to this field`

if `object === null or is_granted("EDIT", object)`. Now, let's think about this. If we're *reading* a `DragonTreasure`, then `object` is *never* going to be `null`. We'll *always* have an object, so the voter will *always* be called. This `object === null` will only happen during *deserialization*, when we're checking to see if we can *write* this field. This *effectively* makes this field *always writable*. That's not really a problem, because we already have `security` up here on `Post()` and `Patch()` that ensures that, in the case of `Patch()`, only the *owner* can edit this object. So basically, once you've passed the `Patch()` `security`, we already know that we can edit this object. Therefore, down here, it's okay to allow us to edit the `isPublished` field.

If that looks a little weird, another workaround is to leave API security off *entirely*. In that case, we would use the *mapper* to handle the `isPublished` field and prevent it from being returned *unless* we're an owner. We could put some security logic right here that basically says:

`Hey, only set the isPublished field on the DTO
if you're the owner. Otherwise, you can leave
isPublished null as a default.`

Long story short, it's good to remember that we *do* have full control of the data via our mapper objects as well.

Okay, let's go back and re-add our security expression. Oh! And let's go back to our mapper as well, because I just realized that we also want to keep that `isPublished` - just not in the `if` statement.

All right, *now* head over, rerun all of the tests, and... oooh! *So close*! We're down to just *one* failure in `testPublishTreasure`. This is our *notification* test. Over here... in `testPublishTreasure`, let's check that out. Earlier, we were testing to see if a notification is created in the database when the status changes from `'isPublished' => false` to `'isPublished' => true`. We did that previously via a custom state processor. We could *also* do that in the mapper. When we're using our `DragonTreasureApiToEntityMapper`, we could check to see if the entity *was* `'isPublished' => false` and is now *changing* to `'isPublished' => true`, and if it *is*, create a notification right there. *But* this doesn't feel like the right place to do that. Data mappers should really just be all about mapping data.

*So* let's try a different solution: Creating a *custom state processor*. Over at you terminal, say:

```terminal
./bin/console make:state-processor
```

And then we'll call that:

```terminal
DragonTreasureStateProcessor
```

There it is! And now we're going to decorate this just like our normal `EntityClassDtoStateProcessor`. We'll add a `__construct()` method with `private EntityClassDtoStateProcessor $innerProcessor`. And down here, we'll just return that with `return $this->innerProcessor->process()` and pass it the arguments it needs: `$data`, `$operation`, `$uriVariables`, and `$context`. Ah, and you can see that this is highlighted in red here. We don't *have to* do this, but this isn't really a `void` method, so we can remove that.

Now that we have this new processor (it's going to use our original one), we can just hook this up inside of here. So on `DragonTreasureApi`, instead of using the *core* processor, we're going to use the `DragonTreasureStateProcessor`.

At this point, we have changed *nothing*, and if we rerun the test, everything still works except for that last failure. *So* let's add our notification code! Earlier, the way we figured out if we're changing from `'isPublished' => false` to `'isPublished => true`, is by using the previous data that's inside of the context. So right here, let's `dd($context['previous_data']`. Now let's head over and run *just* that test:

```terminal
--filter=testPublishTreasure
```

Cool! We can see that our previous data is the `DragonTreasureApi` with `isPublished: false`. This is the *original* one that we had inside of our test when we started. Let's also dump `$data` so our result is even more interesting.

Okay, the original one has `isPublished: false`, and the *new* one has the JSON on it and `isPublished: true`. And just like before, that's what we're going to focus on to send the notification. We wrote some of this code before, so let's go borrow that... *paste*, and that will add a couple of `use` statements. This isn't *super* interesting. We just have the `$previousData`, we're showing that it `isPublished`, and then we're creating a `Notification` down here. The only thing that's *kind of* interesting is that the `Notification` is related to a `DragonTreasure` `$entity`. So we're actually querying for the `$entity` using the `repository` and grabbing the `id` off of the API.

Now we need to inject a couple of things here. The first is `private EntityManagerInterface $entityManager` so we can save, and then `private DragonTreasureRepository $repository`. There we go! That makes a little more sense now. So we're grabbing the `id` off of the `DragonTreasureApi`, *querying* for the `$entity`, and then we relay that on the `Notification` entity and save everything. If we try our test now... it *passes*! And check this out! All of our `DragonTreasure` tests pass. Everything is back where it should be. *Amazing*!

Next: Let's make it possible to *write* the `$dragonTreasures` property on *user*. This involves a trick that's going to help us better understand how API Platform loads data.
