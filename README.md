# CLI Image Helper

A simple CLI program to help with different image related tasks for [MtG-Tutor](http://www.MtG-Tutor.de/)

[![Build Status](https://scrutinizer-ci.com/g/MtGTutor/image-helper/badges/build.png?b=master)](https://scrutinizer-ci.com/g/MtGTutor/image-helper/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MtGTutor/image-helper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MtGTutor/image-helper/?branch=master)

## Usage

``` shell
php imageHelper.php <command> [<args>] [--src-dir=<path>] [--dest-dir=<path>] [--width=<integer>] [--height=<integer>]
                        [--keep] [--debug] [--version] [-k] [-d] [-v]
```

### Commands
 
| Command | Arguments                              | Description                                              |
| ------- | ---------------------------------------| -------------------------------------------------------- |
| minify  | List of folders to minify *(optional)* | Resize image dimensions *(if needed)* and compress image |

### Options

| Name     | Default Value | Description                                                                               |
| -------- | ------------- | ----------------------------------------------------------------------------------------- |
| debug    | `TRUE`        | Shows debug information *(same as d flag)*                                                |
| keep     | `TRUE`        | Do not overwrite files in `src-dir`, instead save result in `dest-dir` *(same as k flag)* |
| dest-dir | `dest`        | Specify a folder where results should be saved                                            |
| src-dir  | `src`         | Specify a folder where images are located                                                 |
| width    | `NULL`        | Specify a width for the image. `NULL` means auto width *(keep aspect ratio)*              |
| height   | `510`         | Specify a height for the image. `NULL` means auto height *(keep aspect ratio)*            |
| version  | `TRUE`        | Shows current Version of the program *(same as v flag)*                                   |

### Flags

| Name     | Description                                                                               |
| -------- | ----------------------------------------------------------------------------------------- |
| d        | Shows debug information *(same as --debug)*                                               |
| k        | Do not overwrite files in `src-dir`, instead save result in `dest-dir` *(same as --keep)* |
| v        | Shows current Version of the program *(same as --version)*                                |

## License
The MIT License (MIT)

Copyright (c) 2015 MtGTutor

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

