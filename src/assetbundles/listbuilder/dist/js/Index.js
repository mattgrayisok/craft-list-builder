/**
 * List Builder plugin for Craft CMS
 *
 * Index Field JS
 *
 * @author    Matt Gray
 * @copyright Copyright (c) 2018 Matt Gray
 * @link      https://mattgrayisok.com
 * @package   ListBuilder
 * @since     1.0.0
 */

$(document).ready( function () {

    $('.js-list-builder-click-submit').click(function(e){
        $(this).parent('form').submit();
    });

    $('#signups-table').DataTable({
        "serverSide": true,
        "ajax": Craft.baseCpUrl + "/list-builder/signups/data"
    });

    $('.js-list-builder-service-selector').click(function(e){
        e.preventDefault();
        var type = $(this).attr('data-type');

        $('.js-list-builder-service-selector').removeClass('sel');
        $(this).addClass('sel');

        $('.js-list-builder-service-pane').css('display', 'none');
        $('.js-list-builder-service-pane[data-type="'+type+'"]').css('display', 'block');
    });

    //Setup graph
    var ctx = $("#signupsChart");
    ctx.height = 400;
    if(ctx.length > 0){
        $.getJSON(Craft.baseCpUrl + "/list-builder/signups/graph").done(function(data){

            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    }
                }
            });
        });
    }

    //Instructions
    $('.js-lb-instructions').click(function(e){
        e.preventDefault();
        var sourceId = $(this).attr('data-source');
        $.get(Craft.baseCpUrl + '/list-builder/sources/instructions/'+sourceId)
        .done(function(data){
            var $div = $(data);
            var myModal = new Garnish.Modal($div);
        }).fail(function(e){
            alert('Error getting instructions');
        })

    })

});
