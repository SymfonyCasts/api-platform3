# Formats

API Platform supports multiple input and output formats. We know that we can go to `/api/treasures.json` to see the JSON, JSON-LD, or HTML format. But adding this extension to the end of the URL is just a hack that API Platform allows. To *formally* choose the format we want the API to return, we're supposed to send an `accept` header, and this is something we can see when we try this out in the documentation. This makes a request with `accept` header set to `application/ld+json`. Setting this header is easy to do in JavaScript, and if you *don't* set it, `application/ld+json` is already the default.

API Platform uses *three* formats out of the box. You can see them down here on the bottom of the documentation page. Why does it support those three formats specifically? Head over to your terminal and run:

```terminal
./bin/console debug:config api_platform
```

If you look over here, you can see a `formats` key which contains those three formats. This basically says that if the `accept` header is `application/ld+json`, use the JSON-LD format. And *internally*, this means that when it's serializing our data, it will tell the serializer to *serialize* to JSON-LD or JSON.

I'm going to add another format just to see if we can. To do that, we just need to add a new item to this config. *But* to make sure that we don't *completely* replace this config, copy those formats and open the `/config/packages` directory. We don't have an `api_platform.yaml` file yet, so let's create one. Inside that, say `api_platform` and paste those in below. And while we don't *have* to do this, we're going to get a little fancy. There's an alternate way to do this configuration that's shorter *and* cooler, so I'll make a few adjustments here and... *awesome*.

If we go and refresh right now, everything seems to work the same. We have the *same* formats below. That's *perfect*, because we've only repeated the default configuration. So, the *new* form we're going to add is another type of JSON called JSONHAL. There's *standard* JSON, and then JSON-LD takes standard JSON and adds more keys and structures to it. JSONHAL is a *competing* standard with JSON-LD. I don't often use JSONHAL, so we're mostly doing this so we can see an example of what adding a format looks like.

If you look up the content type for JSONHAL, it's supposed to be `application/hal+json`. As soon as we do that, when we refresh... it shows *nothing*. I'm pretty sure this is because Symfony doesn't see my new config file. Hop over here and clear the cache with:

```terminal
./bin/console cache:clear
```

Refresh again and... there we go! We now see JSONHAL. And if we click this, it takes us to the JSONHAL version of our documentation. What I want to do here is click on the `GET` request, then "Try it out", and, down here, we can select which of the types we want. I'll select `application/hal+json`, hit "Execute", and... there it is! You can see that it's JSON and it has the same results, but it looks a little different. It has things like `_embedded` and `_links`. That's pretty much all the time we'll spend on JSONHAL. I just wanted to show you what it would look like to add a new format to the system. And the *reason* this works out of the box is, very simply, because the serializer understands the JSONHAL format, and this is one of those built-in formats that API Platform *adds* to the serializer.

Okay, let's do something that's a *little* more practical. What if we want our users to be able to return the treasures in CSV format? CSV is a format that Symfony Serializer understands out of the box. We know that we *could* add CSV right here, but as an extra challenge, instead of enabling CSV for *every* API resource in our system, let's just add it for `DragonTreasure`. Let's find our `ApiResource` attribute and, at the bottom here, add `formats`. Just like with the configuration, if we just put `csv` here, that will remove *all* of the other formats. To do this right, we need to list all of them: `jsonld`, `json`, `html`, and `jsonhal`. Each of these will read the configuration to know which content type to use. At the end, add `csv`. This is a special case, because `csv` doesn't exist in our configuration. We need to tell this which content type will activate the `csv` format, so set this to `text/csv`.

Notice that my editor is mad at me here. It says:

`Named arguments order does not match parameters order`

Remember, when you're passing arguments to attributes, this attribute is actually its own class. We're just filling in the constructor arguments via named arguments. The *order* doesn't really matter. I don't think PHP Storm should be highlighting this as a problem, but if you want, we can click "Sort arguments" and... *there*. It moved `formats` up a little higher, it's happy, and *we* won't have to stare at that yellow underline.

All right, now we can head over, refresh, open up our collection endpoint, hit "Try it out", and, down here, select `text/csv`. Click "Execute" and... *hello* CSV. *Too easy*. Once again, this works because Symfony Serializer *understands* the CSV format, and all of this is handled by the serializer. In fact, if I open the profiler for that request, we can go down to the serializer configuration, and... yep! You can see that it's formatting through the `csv` format using a built-in `CsvEncoder`. That's why we get our nice results. You could do the same thing by adding a ton of other custom formats to the system, depending on your needs. It's *super* flexible.

Next: Let's talk about *validation*.
