# Custom Field

Coming soon...

Let's do something fun. I want to create a totally custom, crazy new feel to Dragon
Treasure or Dragon Treasure API that doesn't exist in our class at all. This is
something else that you can do with a custom normalizer. And since we already have
one setup, I thought we'd use it to also add a completely custom feel. So go to
Dragon Treasure Resource Test and on test owner can see is published field. Let's
also add,

Let's

Rename this to Ken. See is published and is mine fields. So this is really silly, but
we're literally, if we own a dragon treasure, we're gonna add a new bullion property
called is Mine Set to True. So down here at the bottom we'll say is mine and we'll
expect it to be true. So lemme copy that method name, we'll spin over and run our
test with dash. Dash filter equals that method and perfect it's null because it's not
there and we expected it to be true. So how can we add this now that we've gone
through all the pain to get this normalizer set up just right, it's pretty easy. So
what we're gonna do is we're gonna allow the normal Azure system to run that is going
to return to us the normalized data. And then if we want to between that and the
return statement, we can just mess with it. So I'm gonna copy this if statement from
from up here. I could be more clever and reuse code here, but not too worried about
that. So if we're a dragon treasure and we own this dragon treasure, we'll say
normalized and we'll say is mine

Equals true? That's it. When we run our test, it passes. Now a practical downside to
this is that totally custom fields like this are not gonna be something that's
documented in our api. Our API documentation have no idea that something like that
exists. If you do need a super duper custom field like this,

That requires some service logic to figure out its value and you need it to be
documented in your api. You could also solve this with a custom data provider and a
non-persistent property on your your class. So we could add like a new property to
our dragon treasure called is mine, and then a custom state provider. We could
populate that. Now we have not talked about state providers yet. That's how data is
loaded and that's something that we're gonna talk about a little bit more later, but
mostly in a future tutorial. I just wanted to mention that Now, if you're wondering
how you could get a custom field that is also documented. Alright, next, if a user is
allowed to edit something, but there are certain changes to the data that they are
not allowed to make, how do we handle that? Well, that's where security meets
validation.

