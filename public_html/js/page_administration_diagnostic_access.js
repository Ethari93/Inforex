/**
 * Part of the Inforex project
 * Copyright (C) 2013 Michał Marcińczuk, Jan Kocoń, Marcin Ptak
 * Wrocław University of Technology
 */

$(document).ready(function(){
    $("#administration-diagnostic-access-filter").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#administration-diagnostic-access-table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});