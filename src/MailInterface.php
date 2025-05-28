<?php

namespace Tualo\Office\Mail;


interface MailInterface
{

    public function addBCC(string $email, string $name = "");
    public function setFrom(string $email, string $name = "");
    public function addAddress(string $email, string $name = "");
    public function addAttachmentData(string $path, string $content, string $contentType, string $name = "");
    public function addAttachment(string $path, string $name = "");
    public function addReplyTo(string $email, string $name = "");
    public function isHtml($isHtml);
    public function setSubject(string $value);
    public function setAlternativeBody(string $value);
    public function setBody(string $value);
    public function setListUnsubscribePost(string $value);
    public function send();
}
