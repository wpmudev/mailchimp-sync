window.MailChimp = {
    options: {},

    /**
     * Save the responses in every iteration so
     * bulk status can be checked at the end of the process
     */
    bulkActions: [],
    progressbar:null,
    $progressLabel:null,
    processing: false,
    page: 1,
    stats: {
        added:0,
        updated:0,
        errors:0,
        total:0
    },
    init: function( options ) {
        if ( this.processing ) {
            // We are already processing
            return this;
        }

        var labels = jQuery.extend( {
        }, options.labels || {
            initializing: 'Initializing...',
            processed: 'Processed %s / %s',
            checkOperation: 'Checking bulk action result, operation: %s',
            downloadMoreInfo: 'Download more info about bulk operation',
            results: 'Results: Total: %s. Errors: %s'
        } );

        this.options = jQuery.extend( {
            total: 0,
            nonce: '',
            progressbar: '',
            labels: labels,
            progressLabel: ''
        }, options );

        this.$el = jQuery( this.options.progressbar );
        this.$progressLabel = jQuery( this.options.progressLabel );

        this.processing = true;
        this.showProgressBar();
        this.process();
        return this;
    },
    showProgressBar: function() {
        this.progressbar = this.$el.progressbar();
        this.progressbar.progressbar('option', 'value',false);
        this.setLabel( this.options.labels.initializing );
        this.$progressLabel.show();
    },

    process() {
        if ( this.getStat( 'total' ) >= this.options.total ) {
            // Check bulks and finish the process
            this.checkBulkResults();
            return false;
        }

        var mc = this;

        this.call( {
            action:'mailchimp_import',
            page: mc.page
        })
            .done( function(response) {
                if ( ! response.success ) {
                    alert( response.data.message );
                    mc.stop();
                }
                else {
                    mc.page++;
                    mc.updateStat( 'total', mc.getStat( 'total' ) + response.data.processed );
                    mc.addBulkAction(response.data.bulkInfo.operation, response.data.bulkInfo.users );

                    var total = mc.getStat( 'total' );
                    mc.setLabel( mc.options.labels.processed, Math.min( total, mc.options.total ), mc.options.total );
                    mc.process();
                }
            });
    },

    checkBulkResults: function() {
        var action = this.getNextUncheckedBulkAction();
        if ( ! action ) {
            this.finish();
            return;
        }

        this.setLabel( this.options.labels.checkOperation, action.operation );
        var mc = this;

        this.call({
            action: 'mailchimp_check_bulk_results',
            operation: action.operation
        })
            .done( function( response ) {
                if ( response.data.finished ) {
                    // MailChimp has finished with the bulk process
                    var result = response.data.result;
                    mc.updateBulkAction( action.operation, result );

                    // Update stats
                    mc.updateStat( 'errors', mc.getStat( 'errors' ) + result.errored_operations );
                    mc.checkBulkResults();
                }
                else {
                    // MailChimp is still processing, let's wait a little
                    setTimeout(function(){
                        mc.checkBulkResults();
                    }, 5000);
                }
            });
    },

    finish: function() {
        var actions = this.getBulkActions();
        console.log(actions);
        this.progressbar.progressbar( 'destroy' );

        // Show a list of URL links where the user can download extra information
        var urlLinks = jQuery( '<ul></ul>');
        var link;
        var listElem;
        for ( operation in actions ) {
            listElem = jQuery( '<li></li>' );
            link = jQuery( '<a></a>' )
                .attr( 'href', actions[operation].result.response_body_url )
                .text( this.options.labels.downloadMoreInfo + ' ' + operation, operation );

            listElem.append( link );
            urlLinks.append( listElem );
        }
        this.setLabel( this.options.labels.results, this.options.total, this.getStat( 'errors' ) );
        this.$progressLabel.append( urlLinks );
    },

    call: function( data, callback ) {
        var mc = this;
        data.nonce = this.options.nonce;
        return jQuery.post( ajaxurl, data )
            .error( function( response ) {
                console.log( response );
                mc.stop();
            });
    },

    updateStat: function( type, value ) {
        this.stats[ type ] = value;
    },

    getStat: function( type ) {
        if ( this.stats[ type ] ) {
            return this.stats[ type ];
        }

        return 0;
    },

    addBulkAction: function( operation, userIds ) {
        this.bulkActions[operation] = {
            userIds: userIds,
            checked: false,
            result: false,
            operation: operation
        };
    },

    updateBulkAction: function( operation, result ) {
        this.bulkActions[operation].checked = true;
        this.bulkActions[operation].result = result;

        // Update stats
    },

    getBulkActions: function() {
        return this.bulkActions;
    },

    getNextUncheckedBulkAction: function() {
        var actions = this.getBulkActions();
        for ( var operation in actions ) {
            if ( ! actions[ operation ].checked ) {
                return actions[ operation ];
            }
        }
        return false;
    },

    /**
     * Interrupt the process
     */
    stop: function() {
        this.progressbar.progressbar( 'destroy' );
        this.setLabel( '' );
    },

    setLabel: function( label ) {
        for (var i = 1; i < arguments.length; i++) {
            label = label.replace( '%s', arguments[i] );
        }
        this.$progressLabel.html( label );
    }
};
