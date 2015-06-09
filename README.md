# CLI Image Helper

A simple CLI program to help with different image related tasks for MtG-Tutor

[![Build Status](https://scrutinizer-ci.com/g/MtGTutor/image-helper/badges/build.png?b=master)](https://scrutinizer-ci.com/g/MtGTutor/image-helper/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/MtGTutor/image-helper/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/MtGTutor/image-helper/?branch=master)

## Usage

``` shell
php imageHelper.php <command> [<args>] [--version] [--src-dir=<path>] [--dest-dir=<path>] [--keep] [--debug] [-k] [-d] [-v]
```

### Commands
 
| Command | Arguments                              | Description                                              |
| ------- | ---------------------------------------| -------------------------------------------------------- |
| minify  | List of folders to minify *(optional)* | Resize image dimensions *(if needed)* and compress image |

### Options

| Name     | Default Value | Description                                                                               |
| -------- | ------------- | ----------------------------------------------------------------------------------------- |
| debug    | `TRUE`        | Shows debug information *(same as d flag)*                                                |
| dest-dir | `dest`        | Specify a folder where results should be saved                                            |
| keep     | `TRUE`        | Do not overwrite files in `src-dir`, instead save result in `dest-dir` *(same as k flag)* |
| src-dir  | `src`         | Specify a folder where images are located                                                 |
| version  | `TRUE`        | Shows current Version of the program *(same as v flag)*                                   |

### Flags

| Name     | Description                                                                               |
| -------- | ----------------------------------------------------------------------------------------- |
| d        | Shows debug information *(same as --debug)*                                               |
| k        | Do not overwrite files in `src-dir`, instead save result in `dest-dir` *(same as --keep)* |
| v        | Shows current Version of the program *(same as --version)*                                |