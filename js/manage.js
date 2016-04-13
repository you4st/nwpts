$(document).ready(function() {
    // adjust the width of the middle column
    $(function() {
        $("#middle-col").css('width', $(window).width() - 220);
    });

    // for map
    initialize();

    // load the relation data
    var relations = loadRelations();

    // refresh the page when resizing the window
    $(window).on('resize', function() {
        location.reload();
    });

    $("#left-nav").click(function() {
        if ($("#left-col").is(":visible")) {
            $("#left-col").hide("slide");
            $("#left-nav").animate({"left": "-=200"});
            $("#middle-col").animate({"left": "-=200"});
            $("#middle-col").width("+=200");
            $("#left-arrow").attr("src", "/images/icons/arrow-right.png");
        } else {
            $("#left-col").show("slide");
            $("#left-nav").animate({"left": "+=200"});
            $("#middle-col").animate({"left": "+=200"});
            $("#middle-col").animate({"width": "-=200"});
            $("#left-arrow").attr("src", "/images/icons/arrow-left.png");
        }
    });

    $("tr").click(function() {
        if (typeof $(this).attr("id") != "undefined") {
            var id = $(this).attr("id");
            $("#list tr").each(function() {
                if ($(this).attr("id") != id) {
                    $(this).css("background-color", "#ffffff");
                    $(this).hover(function() {
                        $(this).css("background-color", "#eefbe9");
                    }, function() {
                        $(this).css("background-color", "#ffffff");
                    });
                }
            });

            $(this).css("background-color", "#f0a8a8");
            $(this).hover(function() {
                $(this).css("background-color", "#f0a8a8");
            });

            if (!$("#right-col").is(":visible")) {
                $("#right-col").show("slide", {direction: "right"});
                $("#middle-col").animate({"width": "-=500"});
            }

            loadMemberDetails($(this).find('input:checkbox:first').val(), false);
        }
    });

    $("#select_all").on("click", function() {
        var all = $(this);
        $("input:checkbox").each(function() {
            if ($(this).is(":visible")) {
                $(this).prop("checked", all.prop("checked"));
            }
        });
    });

    $("#hide-detail").click(function() {
        $("#right-col").hide("slide", {direction: "right"});
        $("#middle-col").animate({"width": "+=500"});
    })

    $(".tab").click(function() {
        var currCenter = map.getCenter();

        $(".tab").each(function() {
            $(this).removeClass('active');
        });

        $(this).addClass("active");

        $("#member-detail").children().addClass('hide');

        if ($(this).hasClass("personal-tab")) {
            $("#personal-tab").removeClass('hide');
        }

        if ($(this).hasClass("family-tab")) {
            $("#family-tab").removeClass('hide');
        }

        if ($(this).hasClass("map-tab")) {
            $("#map-tab").removeClass('hide');
        }

        google.maps.event.trigger(map, "resize");
        map.setCenter(currCenter);
    });

    $("#remove").click(function() {
        var selected = $("input[name='selected']:checked");

        if (selected.length <= 0) {
            alert("Please select the members to be removed.")
        } else {
            if (confirm("Are you sure to remove the selected members?")) {
                removeMember(selected);
            }
        }
    });

    $("#calc-route").click(function() {
        generateRoute($("#member-address").val());
    });

    $("#search-by-name").click(function() {
        var query = $("input[name='query']").val();

        $.post('/ajax/search-member/', {name: query, list: 1}, function(response) {
            if (response.success) {
                showSelectedRows(response.searchResult);
            }
        }, "json");
    });

    $(".options").change(function() {
        var data = {
            'duty' : $("select[name='duty-filter']").val(),
            'head_of_house' : $("select[name='head-of-house-filter']").val(),
            'age' : $("select[name='age-filter']").val(),
            'registered' : $("select[name='registered-filter']").val(),
            'gender' : $("select[name='gender-filter']").val(),
            'active' : $("select[name='active-filter']").val()
        };

        $.post('/ajax/filter-member/', {data: data}, function(response) {
            if (response.success) {
                showSelectedRows(response.filterResult);
            }
        }, "json");
    });

    $("#filter-reset").click(function() {
        showSelectedRows('reset');

        $("#select-group").find('select').each(function() {
            var options = $(this).find('option');

            options.each(function() {
            	if ($(this).val() == '-1') {
            		$(this).prop('selected', true);
            	}
            });
        });
    });
});

function loadMemberDetails(id, reload) {
	var data = {id: id, reload: 0}

	if (reload) {
		data.reload = 1;
	}

    $.post('/ajax/load-member-details/', data, function(response) {
        if (response.success) {
            $("#personal-tab").html(response.personalInfo);
            $("#family-tab").html(response.familyInfo);
            codeAddress(response.address, response.name);
            $("#member-address").val(response.address);
            $(".show-member-form").on("click", function() {
                $("#static").hide();
                $("#modify").show("slide", {direction: "right"});
            });
            $(".show-member-form").on("click", function() {
                $("#static").hide();
                $("#modify").show("slide", {direction: "right"});
            });
            $(".cancel-modify").on("click", function() {
                $("#modify").hide();
                $("#static").show("slide", {direction: "left"});
            });
            $(".submit-modify").on("click", updateMember);
            $(".bap_radio").on("click", function() {
                if ($(this).val() == 1) {
                    $(this).parent().find(".baptized_on").show(500);
                } else {
                    $(this).parent().find(".baptized_on").hide(500);
                }
            });
            bindOverlay();
        }
    }, "json");
}

function loadScript() {
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&callback=initialize';
    document.body.appendChild(script);
}

function bindButtons() {
    // bind a click event for the "submit" button on the new member overlay
    $(".submit").on("click", registerMember);

    // bind a click event for the "Add Family Member" button on the new member overlay
    $("#add-member").on("click", addRow);

    // bind a click event for the "Remove Line" button on the new member overlay
    $("#remove-line").on("click", deleteRow);

    // bind show/hide event for "세례" radio button on the new member overlay
    $("input[name='baptized']").on("click", function() {
        if ($(this).val() == 1) {
            $(this).parent().find(".baptized_on").show(500);
        } else {
            $(this).parent().find(".baptized_on").hide(500);
        }
    });

    // bind a click event for "search" button on the "add family member" overlay
    $("#search").on("click", searchMember);

    // bind a click event for "add member" button on the "add family member" overlay
    $("#add-family-member").on("click", addFamilyMember);

    // bind a click event for "apply" button on the "change family information" overlay
    $("#change-family-info").on("click", changeFamilyInfo);

    // bind a click event for "upload" button on the "change photo" overlay
    $("#upload-photo").on("click", uploadPhoto);

    // bind a change event for choose file action on the "change photo" overlay
    fileAction();
}

function registerMember() {
    if (validateForm()) {
        var numFamily = 0;
        var data = {
            name : $("input[name='name']").val(),
            email : $("input[name='email']").val(),
            gender : $("input[name='gender']:checked").val(),
            e_last : $("input[name='e_last']").val(),
            e_first : $("input[name='e_first']").val(),
            e_middle : $("input[name='e_middle']").val(),
            birth_month : $("select[name='birth_month']").val(),
            birth_day : $("select[name='birth_day']").val(),
            birth_year : $("select[name='birth_year']").val(),
            birth_lunar : $("input[name='birth_lunar']:checked").val(),
            cell : $("input[name='cell']").val(),
            cell_leader : $("input[name='cell_leader']").is(':checked') ? 1 : 0,
            cell_co_leader : $("input[name='cell_co_leader']").is(':checked') ? 1 : 0,
            duty : $("select[name='duty']").val(),
            home_phone : $("input[name='home_phone']").val(),
            mobile_phone : $("input[name='mobile_phone']").val(),
            active : $("input[name='active']:checked").val(),
            business_phone : $("input[name='business_phone']").val(),
            registered_on : $("input[name='registered_on']").val(),
            marital_status : $("select[name='marital_status']").val(),
            business_name : $("input[name='business_name']").val(),
            baptized : $("input[name='baptized']:checked").val(),
            street : $("input[name='street']").val(),
            city : $("input[name='city']").val(),
            state : $("select[name='state']").val(),
            zip : $("input[name='zip']").val()
        };

        if (data.baptized == 1) {
            data.baptized_on = $("input[name='baptized_on']").val();
        }

        data.nurture = [];

        $("input[name='nurture']:checked").each(function() {
            data.nurture.push($(this).val());
        });

        $("#family-member").find('tr').each(function() {
            numFamily++;
        });

        data.numFamily = numFamily - 1;

        if (numFamily > 1) {
            data.family = {};
            for (var i = 1; i < numFamily; i++) {
                data.family[i] = {
                    name : $("input[name='name-" + i + "']").val(),
                    e_last : $("input[name='e_last-" + i + "']").val(),
                    e_first : $("input[name='e_first-" + i + "']").val(),
                    gender : $("input[name='gender-" + i + "']:checked").val(),
                    relation : $("select[name='relation-" + i + "']").val(),
                    birth_month : $("select[name='birth_month-" + i + "']").val(),
                    birth_day : $("select[name='birth_day-" + i + "']").val(),
                    birth_year : $("select[name='birth_year-" + i + "']").val(),
                    birth_lunar : $("select[name='birth_lunar-" + i + "']").val(),
                    mobile_phone : $("input[name='mobile_phone-" + i + "']").val(),
                    email : $("input[name='email-" + i + "']").val()
                };
            }
        }

        $.post('/disciples/ajax/register-member', data, function(response) {
            if (response.success) {
                window.location.href = '/disciples/manage/reload';
            } else {
                $("p.error").html(response.message)
            }
        }, "json");
    }
}

function updateMember() {

    var memberId = $("input[name='memberId']").val();
    var data = {
        id : memberId,
        name : $("input[name='name_" + memberId + "']").val(),
        email : $("input[name='email_" + memberId + "']").val(),
        gender : $("input[name='gender_" + memberId + "']:checked").val(),
        e_last : $("input[name='e_last_" + memberId + "']").val(),
        e_first : $("input[name='e_first_" + memberId + "']").val(),
        e_middle : $("input[name='e_middle_" + memberId + "']").val(),
        birth_month : $("select[name='birth_month_" + memberId + "']").val(),
        birth_day : $("select[name='birth_day_" + memberId + "']").val(),
        birth_year : $("select[name='birth_year_" + memberId + "']").val(),
        birth_lunar : $("input[name='birth_lunar_" + memberId + "']:checked").val(),
        cell : $("input[name='cell_" + memberId + "']").val(),
        cell_leader : $("input[name='cell_leader_" + memberId + "']").is(':checked') ? 1 : 0,
        cell_co_leader : $("input[name='cell_co_leader_" + memberId + "']").is(':checked') ? 1 : 0,
        baptized : $("input[name='baptized_" + memberId + "']:checked").val(),
        home_phone : $("input[name='home_phone_" + memberId + "']").val(),
        mobile_phone : $("input[name='mobile_phone_" + memberId + "']").val(),
        business_phone : $("input[name='business_phone_" + memberId + "']").val(),
        active : $("input[name='active_" + memberId + "']:checked").val(),
        duty : $("select[name='duty_" + memberId + "']").val(),
        registered_on : $("input[name='registered_on_" + memberId + "']").val(),
        marital_status : $("select[name='marital_status_" + memberId + "']").val(),
        business_name : $("input[name='business_name_" + memberId + "']").val(),
        street : $("input[name='street_" + memberId + "']").val(),
        city : $("input[name='city_" + memberId + "']").val(),
        state : $("select[name='state_" + memberId + "']").val(),
        zip : $("input[name='zip_" + memberId + "']").val()
    };

    if (data.baptized == 1) {
        data.baptized_on = $("input[name='baptized_on_" + memberId + "']").val();
    }

    data.nurture = [];

    $("input[name='nurture_" + memberId + "']:checked").each(function() {
        data.nurture.push($(this).val());
    });

    $.post('/disciples/ajax/update-member', data, function(response) {
        if (response.success) {
            window.location.href = '/disciples/manage/reload';
        } else {
            $("div#modify p.error").html(response.message);
        }
    }, "json");
}

function addFamilyMember() {
    var member_id = $("input[name='memberId']").val();
    var f_member_id = $("input[name='family-member-id']").val();
    $("p.search-error").html('');

    if (member_id == f_member_id) {
        $("p.search-error").html('Can\'t add himself or herself as a family member');
    } else {
        var data = {
            id: member_id,
            new_id: f_member_id,
            relation: $("select[name='relation']").val()
        };

        $.post('/ajax/add-family-member', data, function(response) {
            if (response.success) {
                loadMemberDetails(member_id, false);
                $("#close").click();
            } else {
                $("p.search-error").html(response.message);
            }
        }, "json");
    }
}

function changeFamilyInfo() {
    var memberId = $("input[name='memberId']").val();
    var familyId = $("input[name='familyId']").val();
    var head_of_house = $("input[name='head_of_house']:checked").val();
    var relation = {};
    var remove = [];

    $("input[name='head_of_house']").each(function() {
        var id = $(this).val();
        relation[id] = $("select[name='relation_" + id + "']").val();
    });

    $("input[name='remove_member']:checked").each(function() {
        remove.push($(this).val());
    });

    var data = {
        familyId: familyId,
        head_of_house: head_of_house,
        relation: relation,
        remove: remove
    };

    if (confirm("Are you sure to make the changes for this family?")) {
        $.post('/ajax/change-family-info', data, function(response) {
            if (response.success) {
                loadMemberDetails(memberId, false);
                $("#close").click();
            }
        }, "json");
    }
}

function validateForm() {
    var errorMessage = '';
    var numFamily = 0;

    if ($("input[name='name']").val() == '') {
        errorMessage += '한글 이름\n';
    }

    $("#family-member").find('tr').each(function() {
        numFamily++;
    });

    if (numFamily > 1) {
        for (var i = 1; i < numFamily; i++) {
            if ($("input[name='name-" + i + "']").val() == '') {
                errorMessage += '가족 한글 이름\n';
            }
        }
    }

    if (errorMessage == '') {
        return true;
    } else {
        alert('Please check the following fields:\n\n' + errorMessage);
        return false;
    }
}

function loadRelations() {
	$.post('/ajax/get-family-relation/', {id: 0}, function(response) {
        if (response.success) {
            relations = response.relations;
        }
	});
}

function addRow() {
    var numRow = 0;
    var row = '';

    $("#family-member").find('tr').each(function() {
        numRow++;
    });

    row = '<tr id="member_' + numRow + '">'
        + '<td><input type="text" name="name-' + numRow + '"></td>'
        + '<td><input type="text" name="e_last-' + numRow + '"></td>'
        + '<td><input type="text" name="e_first-' + numRow + '"></td>'
        + '<td><input type="radio" name="gender-' + numRow + '" value="M" checked="checked">남<input type="radio" name="gender-' + numRow + '" value="F">여</td>'
        + '<td><select name="relation-' + numRow + '">' + relations + '</select></td>'
        + '<td>' + getDateOptions(numRow) + '</td>'
        + '<td><input type="text" name="mobile_phone-' + numRow + '"></td>'
        + '<td><input type="text" class="email" name="email-' + numRow + '"></td>'
        + '</tr>';

        $("#family-member").append(row);
}

function deleteRow() {
    var numRow = 0;

    $("#family-member").find('tr').each(function() {
        numRow++;
    });

    if (numRow > 1) {
        $("#member_" + (numRow - 1)).remove();
    }
}

function getDateOptions(numRow) {
    var monthSelect = '<select name="birth_month-' + numRow + '" class="month" value="0"><option>mm</option>';

    for (var i = 1; i <= 12; i++) {
        monthSelect = monthSelect + '<option value="' + i + '">' + i + '</option>';
    }

    monthSelect = monthSelect + '</select>';

    var daySelect = '<select name="birth_day-' + numRow + '" class="day"><option value="0">dd</option>';

    for (var i = 1; i <= 31; i++) {
        daySelect = daySelect + '<option value="' + i + '">' + i + '</option>';
    }

    daySelect = daySelect + '</select>';

    var yearSelect = '<select name="birth_year-' + numRow + '" class="year"><option value="0">yyyy</option>';
    var date = new Date();
    var thisYear = date.getFullYear();
    var endYear = thisYear - 110;

    for (var i = thisYear; i > endYear; i--) {
        yearSelect = yearSelect + '<option value="' + i + '">' + i + '</option>';
    }

    yearSelect = yearSelect + '</select>';

    var lunarSelect = '<select name="birth_lunar-' + numRow + '" class="lunar">'
                    + '<option value="0">양</option>'
                    + '<option value="1">음</option>'
                    + '</select>';

    return monthSelect + daySelect + yearSelect + lunarSelect;
}

function removeMember(selected) {
    var data = [];
    $.each(selected, function() {
        data.push($(this).val());
    });

    $.post('/ajax/remove-member/', {data: data}, function(response) {
        if (response.success) {
            window.location.href = '/disciples/manage/reload';
        }
    }, "json");
}

function searchMember() {
    var query = $("input[name='q_name']").val();

    $.post('/ajax/search-member/', {name: query}, function(response) {
        if (response.success) {
            $("#search_result").html(response.searchResult);
        }
    }, "json");
}

function showSelectedRows(list) {
    // show all rows first
    $("#list").children().find('tr').show();

    // de-select the all checkbox
    $("input:checkbox").each(function() {
        $(this).prop("checked", false);
    });

    if (list != 'reset') {
        $("#list").children().find('tr').each(function() {
            if (typeof $(this).attr('id') != 'undefined') {
                var id_str = $(this).attr('id').split('_');
                var id = id_str[1];

                if ($.inArray(id, list) == -1) {
                    $(this).hide();
                }
            }
        });
    }
}

function uploadPhoto() {
	var formData = new FormData($("form[name='upload']")[0]);
	var id = $("input[name='id']").val();

	console.log(formData);
    $.ajax({
        type: 'POST',
        url: '/ajax/upload-photo',
        timeout: 10000,
        data: formData,
        dataType: "json",
        success: function(response) {
        	if (response.success) {
                loadMemberDetails(id, true);
                $("#close").click();
            } else {
                $("p.upload-error").html(response.message);
            }            
        },
        error: function() {
        	$("p.upload-error").html('There\'s a problem while processing the request. Please try again later.');
        },
        processData: false,
        contentType: false,
        cache: false
    });
}