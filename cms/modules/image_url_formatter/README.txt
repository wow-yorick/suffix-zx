This module adds an FieldFormatter for an Image field,
which let's you grab the URL of an image directly

While most of the code comes from the core module image's
ImageFormatter.php, some rewriting with templating has
been done. Should be stable.

Usage:
(1)
After installation of this module you'll get a new
format type "Image URL" you can assign to any Image Field
in a Content Type. Just go to the Type's "Manage Display"
and choose "Image URL" instead of "Image" from the Format
Combo-Box.

(2)
Same goes for Image Content Fields in Views, choose
"Image URL" as format and an URL Type out of
'Full' (default), 'Absolute' or 'Relative'.

Now the image's URL in the chosen form will be returned
instead of the full <img> tag.

--

As there seems to be an issue with "Global: Custom Text"
at the moment (21/08/15), just use the Template TWIG
for formatting your output or overwrite the Template
in your Profile.
