<?php
namespace Smarttshipping\Shipping\Model\Api;

class Httpstatuscode extends \Magento\Framework\Model\AbstractModel
{
    public function getHttpDtatusCode($errorCode)
    {
        $errorData = [
            '0'=>(string)__('Clyde API Host no found, Unable to connect to clyde at ..'),
            '100'=>(string)__('The request has been received and the process is continuing.'),
            '101'=>(string)__('The server switches protocol.'),

            '200'=>(string)__('The action was successfully received, understood, and accepted.'),
            '201'=>(string)__('The request is complete, and a new resource is created .'),
            '202'=>(string)__('The request is accepted for processing, but the processing is not complete.'),
            '203'=>(string)__('The information in the entity header is from a local or third-party copy, not from the original server.'),
            '204'=>(string)__('A status code and a header are given in the response, but there is no entity-body in the reply.'),
            '205'=>(string)__('The browser should clear the form used for this transaction for additional input.'),
            '206'=>(string)__('The server is returning partial data of the size requested. Used in response to a request specifying a Range header. The server must specify the range included in the response with the Content-Range header.'),

            '300'=>(string)__('Further action must be taken in order to complete the request.'),
            '301'=>(string)__('The requested page has moved to a new url .'),
            '302'=>(string)__('The requested page has moved temporarily to a new url .'),
            '303'=>(string)__('The requested page can be found under a different url .'),
            '304'=>(string)__('This is the response code to an If-Modified-Since or If-None-Match header, where the URL has not been modified since the specified date.'),
            '305'=>(string)__('The requested URL must be accessed through the proxy mentioned in the Location header.'),
            '306'=>(string)__('This code was used in a previous version. It is no longer used, but the code is reserved.'),
            '307'=>(string)__('The requested page has moved temporarily to a new url.'),

            '400'=>(string)__('The request contains incorrect syntax or cannot be fulfilled.'),
            '401'=>(string)__('The requested page needs a username and a password.'),
            '402'=>(string)__('You can not use this code yet.'),
            '403'=>(string)__('Access is forbidden to the requested page.'),
            '404'=>(string)__('The server can not find the requested page.'),
            '405'=>(string)__('The method specified in the request is not allowed.'),
            '406'=>(string)__('The server can only generate a response that is not accepted by the client.'),
            '407'=>(string)__('You must authenticate with a proxy server before this request can be served.'),
            '408'=>(string)__('The request took longer than the server was prepared to wait.'),
            '409'=>(string)__('The request could not be completed because of a conflict.'),
            '410'=>(string)__('The requested page is no longer available .'),
            '411'=>(string)__('The "Content-Length" is not defined. The server will not accept the request without it .'),
            '412'=>(string)__('The pre condition given in the request evaluated to false by the server.'),
            '413'=>(string)__('The server will not accept the request, because the request entity is too large.'),
            '414'=>(string)__('The server will not accept the request, because the url is too long. Occurs when you convert a "post" request to a "get" request with a long query information .'),
            '415'=>(string)__('The server will not accept the request, because the mediatype is not supported .'),
            '416'=>(string)__('The requested byte range is not available and is out of bounds.'),
            '417'=>(string)__('The expectation given in an Expect request-header field could not be met by this server.'),


            '500'=>(string)__('Internal Server Error'),
            '501'=>(string)__('The request was not completed. The server did not support the functionality required.'),
            '502'=>(string)__('The request was not completed. The server received an invalid response from the upstream server.'),
            '503'=>(string)__('The request was not completed. The server is temporarily overloading or down.'),
            '504'=>(string)__('The gateway has timed out.'),
            '505'=>(string)__('The server does not support the "http protocol" version.'),
                            ];
        return isset($errorData[$errorCode])?$errorData[$errorCode]:'';
    }
}
