htmlCleaner
===========

A refactoring (to a class) of PHP Labware's fantastic htmLawed function library 
(<a href="http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/index.php">found here</a>), 
created by Dr. Santosh Patnaik.

**Why?**

Because while htmLawed is a wonderful alternative to HTMLTidy, the source code itself is very difficult to read
and understand. My original assignment was to convert this functionality into a class file, but conversion of
the original files needs to happen first so I can understand what everything is doing.

**What Else?**

The goal of this is not to affect functionality of the htmLawed utility, just code readability. During the conversion 
process, some functionality may be affected for testing purposes only, but it will not stay that way for the finished 
product. Any logic/functionality changes will be shunted off into the class file available in this repo.

**Last little bit**

The original HTMLawed is avaialble through 'Dual licensed with LGPL 3 and GPL 2 or later', and so is this.