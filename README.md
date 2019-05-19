Bulk Import Files (plugin for Omeka)
====================================

[Bulk Import Files] is a plugin for [Omeka Classic] that allows to import files 
in bulk with their internal metadata (for example exif, iptc and xmp for images,
audio and video, or pdf properties, etc.).

This is a backport of the [module Bulk Import Files] for [Omeka S].


Installation
------------

The plugin uses external libraries, [`getid3`] and [`php-pdftk`], so use the
release zip to install it, or use and init the source.

See general end user documentation for [installing a plugin].

* From the zip

Download the last release [`BulkImportFiles.zip`] from the list of releases (the
master does not contain the dependency), and uncompress it in the `plugins`
directory.

* From the source and for development

If the plugin was installed from the source, rename the name of the folder of
the plugin to `BulkImportFiles`, go to the root of the plugin, and run:

```
    composer install
```

The next times:

```
    composer update
```

Then install it like any other Omeka plugin.

* Install pdftk

The command line tool `pdftk` is required to extract data from pdf without raw
xmp data. It should be installed on the server and the path should be set in the
config of the plugin.


Usage
-----

### Configuration

The mapping of each media type (`image/jpg`, `image/png`, `application/pdf`) is
managed via the files inside the folder `data/mapping`.

So the first thing to do is to create mappings will all the needed elements.

For example, for the `JPG` format, the values are the one that are exposed via
the following xml paths (`xmp` is xml and provides all `iptc` and `exif` metadata):

```
Dublin Core : Title = image/jpeg
Dublin Core : Title = /x:xmpmeta/rdf:RDF/rdf:Description/@xmp:Label
Dublin Core : Description = /x:xmpmeta/rdf:RDF/rdf:Description/@xmp:Caption
Dublin Core : Date = /x:xmpmeta/rdf:RDF/rdf:Description/@xmp:CreateDate
Dublin Core : Date Modified = /x:xmpmeta/rdf:RDF/rdf:Description/@xmp:ModifyDate
Dublin Core : Format = /x:xmpmeta/rdf:RDF/rdf:Description/@tiff:Model
Dublin Core : Subject = /x:xmpmeta/rdf:RDF/rdf:Description/dc:subject//rdf:li
```

Note that the first title is used as media type to import files, and the second
as title, if any.

If you prefer to use `exif` or `iptc`, here is the equivalent config:

```
Dublin Core : Title = image/jpeg
Dublin Core : Title = iptc.IPTCApplication.Headline
Dublin Core : Description = iptc.IPTCApplication.Caption
Dublin Core : Date = jpg.exif.EXIF.DateTimeOriginal
Dublin Core : Date Modified = jpg.exif.EXIF.DateTimeDigitized
Dublin Core : Format = jpg.exif.FILE.MimeType
Dublin Core : Subject = iptc.IPTCApplication.Keywords/0
Dublin Core : Subject = iptc.IPTCApplication.Keywords/1
Dublin Core : Subject = iptc.IPTCApplication.Keywords/2
Dublin Core : Subject = iptc.IPTCApplication.Keywords/3
Dublin Core : Subject = iptc.IPTCApplication.Keywords/4
```

Note that metadata can be slighly different between standards.

These items should be kept private, else they will be displayed in public.

Once saved, all the specific items can be checked in the main menu `Bulk import files`
on the main sidebar.

### Assistant to create or update a template

An assistant is available to create or update a mapping via the second
sub-menu. Simply choose a directory where the files you want to import are
located, create your mapping and save it.

The assistant works only with data extractable as an array (getid3 or pdf), not
for xml data, that requires manual edition of xpaths.

### Upload

Once the mappings are ready, you can upload files via the third sub-menu
`Process import`. Just choose the folder where are files to import, then check
and add the files.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [plugins issues] page on GitHub.


License
-------

* Plugin

This plugin is published under the [CeCILL v2.1] licence, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

This software is governed by the CeCILL license under French law and abiding by
the rules of distribution of free software. You can use, modify and/ or
redistribute the software under the terms of the CeCILL license as circulated by
CEA, CNRS and INRIA at the following URL "http://www.cecill.info".

As a counterpart to the access to the source code and rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors have only limited liability.

In this respect, the user’s attention is drawn to the risks associated with
loading, using, modifying and/or developing or reproducing the software by the
user in light of its specific status of free software, that may mean that it is
complicated to manipulate, and that also therefore means that it is reserved for
developers and experienced professionals having in-depth computer knowledge.
Users are therefore encouraged to load and test the software’s suitability as
regards their requirements in conditions enabling the security of their systems
and/or data to be ensured and, more generally, to use and operate it in the same
conditions as regards security.

The fact that you are presently reading this means that you have had knowledge
of the CeCILL license and that you accept its terms.

* Dependencies

See licences of dependencies.


Copyright
---------

* Copyright Daniel Berthereau, 2019


[Bulk Import Files]: https://github.com/Daniel-KM/Omeka-plugin-BulkImportFiles
[Omeka Classic]: https://omeka.org/classic
[Omeka S]: https://omeka.org/s
[module Bulk Import Files]: https://github.com/Daniel-KM/Omeka-S-module-BulkImportFiles
[`getid3`]: https://getid3.org
[`php-pdftk`]: https://github.com/mikehaertl/php-pdftk
[`pdftk`]: https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit
[`BulkImportFiles.zip`]: https://github.com/Daniel-KM/Omeka-plugin-BulkImportFiles/releases
[installing a plugin]: https://omeka.org/classic/docs/Admin/Adding_and_Managing_Plugins/#installing-a-plugin
[plugins issues]: https://github.com/Daniel-KM/Omeka-plugin-BulkImportFiles/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"

