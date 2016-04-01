osx Keychain Bruteforce Php
============
##About
You can use this tool to bruteforce password for os X keychain file. The tool reads the binary format of keychain file so the implementation should be platform independent.

You should fed the list of passwords as a dictionary to the tool.


##How to use:
    $ php brute.php [path to keyChain file] [path to passwordList file]

For the beginning you can download some password dictionaries from [SecLists](https://github.com/danielmiessler/SecLists).

If you've managed to brute the password - you can simply open the keychain file in "Keychain Access" utility and unlock the file with password you've got.


## Example
    $ php brute.php ~/Library/Keychains/testKC2.keychain pwds/10k_most_common.txt
    
    [16:54:54] Keychain file: /Users/alex/Library/Keychains/testKC2.keychain
    [16:54:54] Pwd file: pwds/10k_most_common.txt
    [16:54:54] Passwords to check: 10003
    [16:55:04] time of work: 10 sec, pwds checked: 5317 (53.15%), pwds per sec: 531.7, time left: 00:00:08
    [16:55:11] time of work: 16.74 sec, pwds checked: 8953 (89.5%), pwds per sec: 534.83, time left: 00:00:01
    [16:55:11] [+] supacoolpass seems to be valid

## Testing
To be sure that everything is working ok you can run the test by calling php brute.php test:

    $ php brute.php test

    [17:12:21] Keychain file: test/test.keychain
    [17:12:21] Pwd file: test/pwds.txt
    [17:12:21] Passwords to check: 10
    [17:12:21] time of work: 0.03 sec, pwds checked: 9 (90%), pwds per sec: 300, time left: 00:00:00
    [17:12:21] [+] 123456 seems to be valid

The test keychain is encrypted with 123456 password so you should get this password as result of the test.

## Dependencies
It relies on following PHP extensions:
* [Hash](http://php.net/manual/en/book.hash.php)
* [Mcrypt](http://php.net/manual/en/book.mcrypt.php)

## Contacts
Feel free to email me at antalos@gmail.com

