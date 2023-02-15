# Serialization Tricks

We've sort of tricked the system to allow a `textDescription` field when we send data.
This is made possible thanks to our `setTextDescription()` method, which runs
`nl2br()` on the description that's sent to our API. This means
that the user *sends* a `textDescription` field when editing or creating a
treasure... but they *receive* a `description` field when reading.

[[[ code('a7af4c4e40') ]]]

And that's totally *fine*: you're allowed to have different input fields versus output
fields. But it *would* be a bit cooler if, in this case, *both* were just called
`description`.

## SerializedName: Controlling the Field Name

So... can we control the *name* of a field? *Absolutely*! We do this, as
you may have predicted, via another wonderful attribute. This one is called
`SerializedName`. Pass it `description`:

[[[ code('27bdb7b102') ]]]

This won't change how the field is *read*, but if we refresh the docs... and look
at the `PUT` endpoint... yep! We can now *send* a field called `description`.

## Constructor Arguments

What about constructor arguments in our entity? When we make a `POST` request, for
example, we know it uses the setter methods to write the data onto the properties.

Now try this: find `setName()` and remove it. Then go to the constructor and add a
`string $name` argument there instead. Below, say `$this->name = $name`.

[[[ code('2f2c83cd90') ]]]

From an object-oriented perspective, the field can be passed when the object is
*created*, but after that, it's read-only. Heck, if you wanted to get fancy, you
could add `readonly` to the property.

Let's see what this looks like in our documentation. Open up the `POST` endpoint.
It looks like we can *still* send a `name` field! Test by hitting "Try it out"...
and let's add a `Giant slinky` we won from a real-life giant in... a rather
tense poker match. It's pretty valuable, has a `coolFactor` of `8`, and give it a
`description`. Let's see what happens. Hit "Execute" and... it worked! And we
can see in the response that the `name` *was* set. How is that possible?

Well, if you go down and look at the `PUT` endpoint, you'll see that it *also*
advertises `name` here. But... go up find the id of the treasure we just created -
its 4 for me, put 4 in here to edit... then send *just* the name field to change
it. And... it *didn't* change! Yup, just like with our code, once a `DragonTreasure`
is created, the name *can't* be changed.

But... how did the `POST` request set the name... if there's no setter? The *answer*
is that the serializer is smart enough to set constructor arguments... *if* the
argument name matches the property name. Yup, the fact that the arg is called `name`
and the property is *also* called `name` is what makes this work.

Watch: change the argument to `treasureName` in both places:

[[[ code('b71ce3363b') ]]]

Now, spin over, refresh, and check out the POST endpoint. The field is *gone*. 
API Platform sees that we have a `treasureName` argument that *could* be sent, 
but since `treasureName` doesn't correspond to any property, that field doesn't 
have any serialization groups. So it's not used. I'll change that back to `name`:

[[[ code('2ed74f72ac') ]]]

By using `name`, it looks at the `name` property, and reads *its* serialization
groups.

## Optional Vs Required Constructor Args

However, there *is* still one problem with constructor arguments that you should be
aware of. Refresh the docs.

What would happen if our user *doesn't* pass a `name` at all? Hit "Execute" to
find out. Ok! We get an error with a 400 status code... *but* it's not a very *good*
error. It says:

> Cannot create an instance of `App\Entity\DragonTreasure` from serialized data
> because its constructor requires parameter `name` to be present.

That's... actually *too* technical. What we *really* want is to allow *validation*
to take care of this... and we'll talk about validation soon. But in order for
validation to work, the serializer needs to be able to do its job: it needs to
be able to *instantiate* the object:

[[[ code('089e7c4dc2') ]]]

Ok, try this now... better! Ok, it's *worse* - a 500 error - but we'll fix that
with validation in a few minutes. The point is: the serializer *was* able to
create our object.

Next: To help us while we're developing, let's add a rich set of data fixtures. Then
we'll play with a great feature that API Platform gives us for *free*: *pagination*
