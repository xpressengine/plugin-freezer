{{ app('xe.frontend')->js('assets/core/xe-ui-component/js/xe-page.js')->load() }}
{{ app('xe.frontend')->js('assets/vendor/jqueryui/jquery-ui.min.js')->load() }}
{{ app('xe.frontend')->css('assets/vendor/jqueryui/jquery-ui.min.css')->load() }}

<div class="row">
    <div class="col-sm-12">
        <div class="panel-group">
            <div class="panel">
                <div class="panel-heading">
                    <div class="pull-left">
                        <h3 class="panel-title">{{xe_trans('freezer::freezerWorkLog')}}</h3>
                    </div>
                </div>

                <div class="panel-heading">
{{--                    <div class="pull-left">--}}
{{--                        <div class="btn-group btn-fillter" role="group">--}}
{{--                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">--}}
{{--                                <span class="caret"></span>--}}
{{--                            </button>--}}
{{--                            <ul class="dropdown-menu" role="menu">--}}
{{--                                <li><strong>{{xe_trans('xe::approve')}}/{{xe_trans('xe::deny')}}</strong></li>--}}
{{--                            </ul>--}}
{{--                        </div>--}}
{{--                    </div>--}}

                    <div class="pull-right">
                        <div class="input-group search-group">
                            <form method="GET" action="{{ route('freezer::setting.log') }}" accept-charset="UTF-8" role="form" id="_search-form" class="form-inline">
                                <div class="form-group input-group-btn">
                                    <div class="input-group">
                                        <span class="input-group-addon">{{xe_trans('freezer::searchPeriod')}}</span>
                                        <input type="text" id="startDatePicker" name="startDate" class="form-control" value="{{ Request::get('startDate') }}">
                                        <input type="text" id="endDatePicker" name="endDate" class="form-control" value="{{ Request::get('endDate') }}">
                                    </div>
                                </div>
                                @foreach(Request::except(['page','startDate', 'endDate']) as $name => $value)
                                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                @endforeach
                            </form>
                        </div>
                    </div>
                </div>

                <div>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th scope="col">{{xe_trans('freezer::workTime')}}</th>
                            <th scope="col">{{xe_trans('freezer::workType')}}</th>
                            <th scope="col">{{xe_trans('freezer::workResult')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>{{$log->created_at}}</td>
                                <td>{{$log->action }}</td>
                                <td>{{$log->result}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if($pagination = $logs->render())
                    <div class="panel-footer">
                        <div class="pull-left">
                            <nav>
                                {!! $pagination !!}
                            </nav>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $("#startDatePicker").datepicker({
            dateFormat: "yy-mm-dd",
            maxDate: 0,
        });

        $("#endDatePicker").datepicker({
            dateFormat: "yy-mm-dd",
        });

        $("#startDatePicker").change(function () {
            setEndDatePickerSetDate($(this).datepicker('getDate'));
            setEndDatePickerMinDate($(this).datepicker('getDate'));

            $(this).closest('form').submit();
        });

        $("#endDatePicker").change(function () {
            $(this).closest('form').submit();
        });

        initDatePicker();
    });

    function initDatePicker() {
        var startDate = $("#startDatePicker").val();

        if (startDate != '') {
            setEndDatePickerMinDate($("#startDatePicker").datepicker('getDate'));
        }
    }

    function setEndDatePickerSetDate(newDate) {
        newDate.setMonth(newDate.getMonth() + 1);
        $("#endDatePicker").datepicker("setDate", newDate);
    }

    function setEndDatePickerMinDate(minDate) {
        minDate.setDate(minDate.getDate());
        $("#endDatePicker").datepicker('option',{minDate:minDate});
    }
</script>
