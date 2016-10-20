<?php


class WSSE {

    public static function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }


    public static function generateWsse($password, $email){
        $nonce = hash_hmac('sha512', uniqid(null, true), uniqid(), true);
        $created = new \DateTime('now', new \DateTimezone('UTC'));
        $created = $created->format(\DateTime::ISO8601);
        $digest = sha1($nonce.$created.$password, true);

        return sprintf(
            'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
            $email,
            base64_encode($digest),
            base64_encode($nonce),
            $created
        );
    }

    public static function encodePassword($password, $salt){
        $parent = new MessageDigestPasswordEncoder();
        $encoded =  $parent->encodePassword($password, $salt);

        return $encoded;

    }

}
