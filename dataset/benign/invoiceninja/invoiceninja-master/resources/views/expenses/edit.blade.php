@extends('header')

@section('head')
    @parent

        @include('money_script')

        <style type="text/css">
            .input-group-addon {
                min-width: 40px;
            }
        </style>
@stop

@section('content')

	{!! Former::open($url)
            ->addClass('warn-on-exit main-form')
            ->onsubmit('return onFormSubmit(event)')
            ->autocomplete('off')
            ->method($method) !!}
    <div style="display:none">
        {!! Former::text('action') !!}
        {!! Former::text('data')->data_bind('value: ko.mapping.toJSON(model)') !!}
    </div>

	@if ($expense)
		{!! Former::populate($expense) !!}
        {!! Former::populateField('should_be_invoiced', intval($expense->should_be_invoiced)) !!}

        <div style="display:none">
            {!! Former::text('public_id') !!}
            {!! Former::text('invoice_id') !!}
        </div>
	@endif

    <div class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">

    				{!! Former::select('vendor_id')->addOption('', '')
                            ->label(trans('texts.vendor'))
                            ->addGroupClass('vendor-select') !!}

                    {!! Former::select('expense_category_id')->addOption('', '')
                            ->label(trans('texts.category'))
                            ->addGroupClass('expense-category-select') !!}

                    {!! Former::text('amount')
                            ->label(trans('texts.amount'))
                            ->data_bind("value: amount, valueUpdate: 'afterkeydown'")
                            ->addGroupClass('amount')
                            ->append('<span data-bind="html: expenseCurrencyCode"></span>') !!}

                    {!! Former::select('expense_currency_id')->addOption('','')
                            ->data_bind('combobox: expense_currency_id')
                            ->label(trans('texts.currency_id'))
                            ->data_placeholder(Utils::getFromCache($account->getCurrencyId(), 'currencies')->getTranslatedName())
                            ->fromQuery($currencies, 'name', 'id') !!}

                    @if (! $isRecurring)
                        {!! Former::text('expense_date')
                                ->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT, DEFAULT_DATE_PICKER_FORMAT))
                                ->addGroupClass('expense_date')
                                ->label(trans('texts.date'))
                                ->append('<i class="glyphicon glyphicon-calendar"></i>') !!}
                    @endif

                    @if ($expense && $expense->invoice_id)
                        {!! Former::plaintext()
                                ->label('client')
                                ->value($expense->client->present()->link)  !!}
                    @else
                        {!! Former::select('client_id')
                                ->addOption('', '')
                                ->label(trans('texts.client'))
                                ->data_bind('combobox: client_id')
                                ->addGroupClass('client-select') !!}
                    @endif

                    @include('partials/custom_fields', ['entityType' => ENTITY_EXPENSE])

                    @if (count($taxRates))
                        @if (!$expense || ($expense && (!$expense->tax_name1 && !$expense->tax_name2)))
                            {!! Former::checkbox('apply_taxes')
                                    ->text(trans('texts.apply_taxes'))
                                    ->data_bind('checked: apply_taxes')
                                    ->label(' ')
                                    ->value(1) !!}
                        @endif
                    @endif

                    <div style="display:none" data-bind="visible: apply_taxes">
                        <br/>
                        @include('partials.tax_rates')
                    </div>

                    @if (!$expense || ($expense && !$expense->invoice_id))
                        {!! Former::checkbox('should_be_invoiced')
                                ->text(trans('texts.mark_billable'))
                                ->data_bind('checked: should_be_invoiced()')
                                ->label(' ')
                                ->value(1) !!}
                    @endif

                    @if ($isRecurring)

                        {!! Former::select('frequency_id')
                                ->label('frequency')
                                ->options(\App\Models\Frequency::selectOptions())
                                ->data_bind("value: frequency_id") !!}
                        {!! Former::text('start_date')
                                ->data_bind("datePicker: start_date, valueUpdate: 'afterkeydown'")
    							->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT, DEFAULT_DATE_PICKER_FORMAT))
                                ->appendIcon('calendar')
                                ->addGroupClass('start_date')
                                ->data_date_start_date($expense ? false : $account->formatDate($account->getDateTime())) !!}
                        {!! Former::text('end_date')
                                ->data_bind("datePicker: end_date, valueUpdate: 'afterkeydown'")
    							->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT, DEFAULT_DATE_PICKER_FORMAT))
                                ->appendIcon('calendar')
                                ->addGroupClass('end_date')
                                ->data_date_start_date($expense ? false : $account->formatDate($account->getDateTime())) !!}

                    @else
                        @if ((! $expense || ! $expense->transaction_id))

                            @if (! $expense || ! $expense->isPaid())
                                {!! Former::checkbox('mark_paid')
                                        ->data_bind('checked: mark_paid')
                                        ->text(trans('texts.mark_expense_paid'))
                                        ->label(' ')
                                        ->value(1) !!}
                            @endif

                            <div style="display:none" data-bind="visible: mark_paid">
                                {!! Former::select('payment_type_id')
                                        ->addOption('','')
                                        ->fromQuery($paymentTypes, 'name', 'id')
                                        ->addGroupClass('payment-type-select') !!}

                                {!! Former::text('payment_date')
                                        ->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT))
                                        ->addGroupClass('payment_date')
                                        ->append('<i class="glyphicon glyphicon-calendar"></i>') !!}

                                {!! Former::text('transaction_reference') !!}
                            </div>
                        @endif

                        @if (!$expense || ($expense && ! $expense->isExchanged()))
                            {!! Former::checkbox('convert_currency')
                                    ->text(trans('texts.convert_currency'))
                                    ->data_bind('checked: convert_currency')
                                    ->label(' ')
                                    ->value(1) !!}
                        @endif


                        <div style="display:none" data-bind="visible: enableExchangeRate">
                            <br/>
                            <span style="display:none" data-bind="visible: !client_id()">
                                {!! Former::select('invoice_currency_id')->addOption('','')
                                        ->label(trans('texts.invoice_currency'))
                                        ->data_placeholder(Utils::getFromCache($account->getCurrencyId(), 'currencies')->name)
                                        ->data_bind('combobox: invoice_currency_id, disable: true')
                                        ->fromQuery($currencies, 'name', 'id') !!}
                            </span>
                            <span style="display:none;" data-bind="visible: client_id">
                                {!! Former::plaintext('')
                                        ->value('<span data-bind="html: invoiceCurrencyName"></span>')
                                        ->style('min-height:46px')
                                        ->label(trans('texts.invoice_currency')) !!}
                            </span>

                            {!! Former::text('exchange_rate')
                                    ->data_bind("value: exchange_rate, enable: enableExchangeRate, valueUpdate: 'afterkeydown'") !!}

                            {!! Former::text('invoice_amount')
                                    ->addGroupClass('converted-amount')
                                    ->data_bind("value: convertedAmount, enable: enableExchangeRate")
                                    ->append('<span data-bind="html: invoiceCurrencyCode"></span>') !!}
                        </div>

                        @if ($account->hasFeature(FEATURE_DOCUMENTS))
                            {!! Former::checkbox('invoice_documents')
                                    ->text(trans('texts.add_documents_to_invoice'))
                                    ->onchange('onInvoiceDocumentsChange()')
                                    ->data_bind('checked: invoice_documents')
                                    ->label(' ')
                                    ->value(1) !!}
                        @endif

                    @endif


	            </div>
                <div class="col-md-6">

                    {!! Former::textarea('private_notes')->rows(! $isRecurring && $account->hasFeature(FEATURE_DOCUMENTS) ? 6 : 10) !!}
                    {!! Former::textarea('public_notes')->rows(! $isRecurring && $account->hasFeature(FEATURE_DOCUMENTS) ? 6 : 10) !!}

                    @if (! $isRecurring && $account->hasFeature(FEATURE_DOCUMENTS))
                        <div class="form-group">
                            <label for="public_notes" class="control-label col-lg-4 col-sm-4">
                                {{trans('texts.documents')}}
                            </label>
                            <div class="col-lg-8 col-sm-8">
                                <div role="tabpanel" class="tab-pane" id="attached-documents" style="position:relative;z-index:9">
                                    <div id="document-upload">
                                        <div class="dropzone">
                                            <div data-bind="foreach: documents">
                                                <input type="hidden" name="document_ids[]" data-bind="value: public_id"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <center class="buttons">
        {!! Button::normal(trans('texts.cancel'))
                ->asLinkTo(HTMLUtils::previousUrl('/expenses'))
                ->appendIcon(Icon::create('remove-circle'))
                ->large() !!}

        @if (Auth::user()->canCreateOrEdit(ENTITY_EXPENSE, $expense))
            @if (Auth::user()->hasFeature(FEATURE_EXPENSES))
                @if (!$expense || !$expense->is_deleted)
                    {!! Button::success(trans('texts.save'))
                            ->appendIcon(Icon::create('floppy-disk'))
                            ->large()
                            ->submit() !!}
                @endif

                @if ($expense && !$expense->trashed())
                    {!! DropdownButton::normal(trans('texts.more_actions'))
                          ->withContents($actions)
                          ->large()
                          ->dropup() !!}
                @endif

                @if ($expense && $expense->trashed())
                    {!! Button::primary(trans('texts.restore'))
                            ->withAttributes(['onclick' => 'submitAction("restore")'])
                            ->appendIcon(Icon::create('cloud-download'))
                            ->large() !!}
                @endif

            @endif
        @endif
    </center>

	{!! Former::close() !!}

    <script type="text/javascript">
        var vendors = {!! $vendors !!};
        var clients = {!! $clients !!};
        var categories = {!! $categories !!};

        var clientMap = {};
        var vendorMap = {};
        var categoryMap = {};

        for (var i=0; i<clients.length; i++) {
            var client = clients[i];
            clientMap[client.public_id] = client;
        }

        function onFormSubmit(event) {
            if (window.countUploadingDocuments > 0) {
                swal({!! json_encode(trans('texts.wait_for_upload')) !!});
                return false;
            }

            @if (Auth::user()->canCreateOrEdit(ENTITY_EXPENSE, $expense))
                return true;
            @else
                return false
            @endif
        }

        function onClientChange() {
            var clientId = $('select#client_id').val();
            var client = clientMap[clientId];
            if (client) {
                model.invoice_currency_id(client.currency_id);
                model.updateExchangeRate();
            }
        }

        function submitAction(action, invoice_id) {
            $('#action').val(action);
            $('#invoice_id').val(invoice_id);
            $('.main-form').submit();
        }

        function onDeleteClick() {
            sweetConfirm(function() {
                submitAction('delete');
            });
        }

        $(function() {
            var vendorId = {{ $vendorPublicId ?: 0 }};
            var $vendorSelect = $('select#vendor_id');
            @if (Auth::user()->can('create', ENTITY_VENDOR))
                $vendorSelect.append(new Option("{{ trans('texts.create_vendor')}}: $name", '-1'));
            @endif
            for (var i = 0; i < vendors.length; i++) {
                var vendor = vendors[i];
                vendorMap[vendor.public_id] = vendor;
                $vendorSelect.append(new Option(getClientDisplayName(vendor), vendor.public_id));
            }
            @include('partials/entity_combobox', ['entityType' => ENTITY_VENDOR])
            if (vendorId) {
                var vendor = vendorMap[vendorId];
                setComboboxValue($('.vendor-select'), vendor.public_id, vendor.name);
            }

            var categoryId = {{ $categoryPublicId ?: 0 }};
            var $expense_categorySelect = $('select#expense_category_id');
            @if (Auth::user()->can('create', ENTITY_EXPENSE_CATEGORY))
                $expense_categorySelect.append(new Option("{{ trans('texts.create_expense_category')}}: $name", '-1'));
            @endif
            for (var i = 0; i < categories.length; i++) {
                var category = categories[i];
                categoryMap[category.public_id] = category;
                $expense_categorySelect.append(new Option(category.name, category.public_id));
            }
            @include('partials/entity_combobox', ['entityType' => ENTITY_EXPENSE_CATEGORY])
            if (categoryId) {
                var category = categoryMap[categoryId];
                setComboboxValue($('.expense-category-select'), category.public_id, category.name);
            }

            $('#expense_date').datepicker('update', '{{ $expense ? Utils::fromSqlDate($expense->expense_date) : 'new Date()' }}');

            $('.expense_date .input-group-addon').click(function() {
                toggleDatePicker('expense_date');
            });

            var $clientSelect = $('select#client_id');
            for (var i=0; i<clients.length; i++) {
                var client = clients[i];
                var clientName = getClientDisplayName(client);
                if (!clientName) {
                    continue;
                }
                $clientSelect.append(new Option(clientName, client.public_id));
            }
            $clientSelect.combobox({highlighter: comboboxHighlighter}).change(function() {
                onClientChange();
            });

            $('#invoice_currency_id, #expense_currency_id').on('change', function() {
                setTimeout(function() {
                    model.updateExchangeRate();
                }, 1);
            })

            @if ($data)
                // this means we failed so we'll reload the previous state
                window.model = new ViewModel({!! $data !!});
            @else
                // otherwise create blank model
                window.model = new ViewModel({!! $expense !!});
            @endif
            ko.applyBindings(model);

            @if (!$expense && $clientPublicId)
                onClientChange();
            @endif

            @if (!$vendorPublicId)
                $('.vendor-select input.form-control').focus();
            @else
                $('#amount').focus();
            @endif

            @if ($isRecurring)
                $('#start_date, #end_date').datepicker();
                @if ($expense && $expense->start_date)
                    $('#start_date').datepicker('update', '{{ $expense && $expense->start_date ? Utils::fromSqlDate($expense->start_date) : 'new Date()' }}');
                @elseif (! $expense)
                    $('#start_date').datepicker('update', new Date());
                @endif
                @if ($expense && $expense->end_date)
                    $('#end_date').datepicker('update', '{{ Utils::fromSqlDate($expense->end_date) }}');
                @endif

                $('.start_date .input-group-addon').click(function() {
                    toggleDatePicker('start_date');
                });
                $('.end_date .input-group-addon').click(function() {
                    toggleDatePicker('end_date');
                });
            @else
                $('#payment_type_id').combobox();
                $('#mark_paid').click(function(event) {
                    if ($('#mark_paid').is(':checked')) {
                        $('#payment_date').datepicker('update', new Date());
                        @if ($account->payment_type_id)
                            setComboboxValue($('.payment-type-select'), {{ $account->payment_type_id }}, "{{ trans('texts.payment_type_' . $account->payment_type->name) }}");
                        @endif
                    } else {
                        $('#payment_date').datepicker('update', false);
                        setComboboxValue($('.payment-type-select'), '', '');
                    }
                })

                @if ($expense && $expense->payment_date)
                    $('#payment_date').datepicker('update', '{{ Utils::fromSqlDate($expense->payment_date) }}');
                @endif

                $('.payment_date .input-group-addon').click(function() {
                    toggleDatePicker('payment_date');
                });

                @if (Auth::user()->account->hasFeature(FEATURE_DOCUMENTS))
                    $('.main-form').submit(function(){
                        if($('#document-upload .fallback input').val())$(this).attr('enctype', 'multipart/form-data')
                        else $(this).removeAttr('enctype')
                    })

                    @include('partials.dropzone', ['documentSource' => 'model.documents()'])
                @endif
            @endif
        });

        var ViewModel = function(data) {
            var self = this;

            self.expense_currency_id = ko.observable();
            self.invoice_currency_id = ko.observable();
            self.documents = ko.observableArray();
            self.amount = ko.observable();
            self.exchange_rate = ko.observable(1);
            self.should_be_invoiced = ko.observable();
            self.apply_taxes = ko.observable({{ ($expense && ($expense->tax_name1 || $expense->tax_name2)) ? 'true' : 'false' }});

            @if ($isRecurring)
                self.frequency_id = ko.observable({{ FREQUENCY_MONTHLY }});
                self.start_date = ko.observable();
                self.end_date = ko.observable();
            @else
                self.convert_currency = ko.observable({{ ($expense && $expense->isExchanged()) ? 'true' : 'false' }});
                self.mark_paid = ko.observable({{ $expense && $expense->isPaid() ? 'true' : 'false' }});
            @endif

            var invoiceDocuments = false;
            if (isStorageSupported()) {
                invoiceDocuments = localStorage.getItem('last:invoice_documents');
            }
            self.invoice_documents = ko.observable({{ $expense ? $expense->invoice_documents : 'invoiceDocuments' }});

            self.mapping = {
                'documents': {
                    create: function(options) {
                        return new DocumentModel(options.data);
                    }
                }
            }

            if (data) {
                ko.mapping.fromJS(data, self.mapping, this);
            }

            self.account_currency_id = ko.observable({{ $account->getCurrencyId() }});
            self.client_id = ko.observable({{ $clientPublicId }});
            //self.vendor_id = ko.observable({{ $vendorPublicId }});
            //self.expense_category_id = ko.observable({{ $categoryPublicId }});

            self.convertedAmount = ko.computed({
                read: function () {
                    return roundToTwo(self.amount() * self.exchange_rate()).toFixed(2);
                },
                write: function(value) {
                    // When changing the converted amount we're updating
                    // the exchange rate rather than change the amount
                    self.exchange_rate(roundSignificant(NINJA.parseFloat(value) / self.amount()));
                    //self.amount(roundToTwo(value / self.exchange_rate()));
                }
            }, self);

            self.updateExchangeRate = function() {
                var fromCode = self.expenseCurrencyCode();
                var toCode = self.invoiceCurrencyCode();
                if (currencyMap[fromCode].exchange_rate && currencyMap[toCode].exchange_rate) {
                    var rate = fx.convert(1, {
                        from: fromCode,
                        to: toCode,
                    });
                    self.exchange_rate(roundToFour(rate, true));
                } else {
                    self.exchange_rate(1);
                }
            }

            self.getCurrency = function(currencyId) {
                return currencyMap[currencyId || self.account_currency_id()];
            };

            self.expenseCurrencyCode = ko.computed(function() {
                return self.getCurrency(self.expense_currency_id()).code;
            });

            self.invoiceCurrencyCode = ko.computed(function() {
                return self.getCurrency(self.invoice_currency_id()).code;
            });

            self.invoiceCurrencyName = ko.computed(function() {
                return self.getCurrency(self.invoice_currency_id()).name;
            });

            self.enableExchangeRate = ko.computed(function() {
                if (self.convert_currency && self.convert_currency()) {
                    return true;
                }
                var expenseCurrencyId = self.expense_currency_id() || self.account_currency_id();
                var invoiceCurrencyId = self.invoice_currency_id() || self.account_currency_id();
                return expenseCurrencyId != invoiceCurrencyId
                    || invoiceCurrencyId != self.account_currency_id()
                    || expenseCurrencyId != self.account_currency_id();
            })

            self.addDocument = function() {
                var documentModel = new DocumentModel();
                self.documents.push(documentModel);
                return documentModel;
            }

            self.removeDocument = function(doc) {
                 var public_id = doc.public_id?doc.public_id():doc;
                 self.documents.remove(function(document) {
                    return document.public_id() == public_id;
                });
            }
        };
        function DocumentModel(data) {
            var self = this;
            self.public_id = ko.observable(0);
            self.size = ko.observable(0);
            self.name = ko.observable('');
            self.type = ko.observable('');
            self.url = ko.observable('');

            self.update = function(data){
                ko.mapping.fromJS(data, {}, this);
            }

            if (data) {
                self.update(data);
            }
        }

        function addDocument(file) {
            file.index = model.documents().length;
            model.addDocument({name:file.name, size:file.size, type:file.type});
    	}

    	function addedDocument(file, response) {
            model.documents()[file.index].update(response.document);
    	}

    	function deleteDocument(file) {
            model.removeDocument(file.public_id);
    	}

        function onInvoiceDocumentsChange() {
            if (isStorageSupported()) {
                var checked = $('#invoice_documents').is(':checked');
                localStorage.setItem('last:invoice_documents', checked || '');
            }
        }

    </script>

@stop
