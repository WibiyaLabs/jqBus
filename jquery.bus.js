/**
 * The jqBus is based on jQuery ajax capabilities and a server side handler.
 * The jqBus is working with an "envelope" object and returning a unified response object.
 * The envelope structure example:
 * var envelope = {service: 'MyService', method: 'doSomething', callback: myCallback, data: [param1,param2]}
 * service - server side class name
 * method - a method inside the server side class
 * callback - javascript function to call when the operation is completed (success and failure) with the bus response object
 * data - (optional) - array of parameters to send to the class method
 *
 * Bus response object:
 * {result: 'success|failure', data: {}}
 * result - consist of the text success or failure which indicate the operation result
 * data - mixed. can be any data type which was serialized by the server side bus handler.
 *        In case of an failure result, the data might have an error description, if available
 *
 * @author Itzik Paz
 */


// create the jqBus object only once
if (!window.jqBus) {
    /**
     * jqBus namespace
     */
    window.jqBus = {
        /**
         * Default url for the bus server side handler
         */
        url:'/bus.php',
        /**
         * Response result codes
         */
        result:{
            /**
             * Unknown result code
             */
            unknown:0,
            /**
             * Operation completed successfully
             */
            success:1,
            /**
             * Failure occur while performing the operation
             */
            failure:2,
            /**
             * The operation has timed out
             */
            timeout:3
        },
        /**
         * List of errors
         */
        errors:[]
    };
}

(function () {
    /**
     * Check if an envelope object is valid
     * @param envelope  Object  Bus message envelope
     */
    function validateEnvelope(envelope) {
        if (envelope == null) {
            return "invalid object";
        }

        if (envelope.service == null || envelope.service == '') {
            return "invalid service";
        }

        if (envelope.method == null || envelope.method == '') {
            return "invalid method";
        }

        if (envelope.callback == null || typeof envelope.callback != 'function') {
            return "invalid callback";
        }

        return true;
    }

    /**
     * Invoke the server bus handler
     * @param envelope  Object   Bus message envelope
     */
    jqBus.makeRequest = function (envelope) {
        var validate = validateEnvelope(envelope);
        if (validate !== true) {
            jqBus.errors.push(validate);
            return false;
        }

        jQuery.ajax({
            url:jqBus.url,
            cache:false,
            data:{json:JSON.stringify(envelope)},
            dataType:"json",
            type:"POST",
            success:function (response) {
                envelope.callback(response);
                return true;
            },
            error:function (XMLHttpRequest, textStatus, errorThrown) {
                var result = {
                    "result":jqBus.result.unknown,
                    "data":""
                };
                if (textStatus == null) {
                    result.result = jqBus.result.failure;
                    result.message = "unknown failure";
                }
                else {
                    switch (textStatus) {
                        case "timeout":
                            result.result = jqBus.result.timeout;
                            result.data = "timout";
                            break;
                        case "notmodified":
                            result.result = jqBus.result.failure;
                            result.data = (errorThrown != null) ? errorThrown : "not modified";
                            break;
                        case "parsererror":
                            result.result = jqBus.result.failure;
                            result.data = (errorThrown != null) ? errorThrown : "parse error";
                            break;
                        case "error":
                            result.result = jqBus.result.failure;
                            result.data = (errorThrown != null) ? errorThrown : "unknown failure";
                            break;
                        default:
                            result.result = jqBus.result.failure;
                            result.data = (errorThrown != null) ? errorThrown : "unknown failure";
                            break;
                    }

                    jqBus.errors.push(result.message);
                    envelope.callback(result);
                    return false;
                }

            }
        });
    }
})();