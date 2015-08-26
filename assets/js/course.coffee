$ ->
  $('form#create-review-form input[type=checkbox]#anonymous').change ->
    $('form#create-review-form #anonymous-warning').removeClass('hidden') if $(@).is(':checked')

  modal = $('#edit-review-modal')

  showEditModal = (openingLink) ->
    reviewElement = openingLink.closest('div.review')
    reviewId = openingLink.data('review-id')
    reviewGrades =
      exercises: openingLink.data('review-exercises-grade')
      lectures: openingLink.data('review-lectures-grade')
      content: openingLink.data('review-content-grade')

    reviewContent = reviewElement.find('p.review-content').text()
    reviewTitle = openingLink.data('review-title')
    reviewDifficulty = openingLink.data('review-difficulty') || 0
    reviewAnonymous = openingLink.data('review-anonymous')

    window.location.hash = "#!edit-" + reviewId

    modal.find('input[name=review_id]').val(reviewId)
    modal.find('input[name=title]').val(reviewTitle)
    modal.find('textarea[name=comment]').val(reviewContent)
    modal.find('input[name=difficulty][value='+reviewDifficulty+']').attr('checked', 'checked')

    modal.find('input#anonymous').attr('checked', 'checked') if reviewAnonymous == 1

    for gradeType, grade of reviewGrades
      modal.find('[data-starbar^=' + gradeType + '_grade]').data('starbar').setValue(grade)

    modal.modal('show')

  if (window.location.hash.indexOf('!edit') == 1)
    id = window.location.hash.split("-")[1]
    showEditModal($('a.edit-review[data-review-id='+id+']').first())

  # Dirty hack: xedit means the controller lead us here after a failed update.
  # PHP did the work of hydrating so we should not overwrite with review data.
  if (window.location.hash.indexOf('!xedit') == 1)
    modal.modal('show')
    reviewId = window.location.hash.split("-")[1]
    modal.find('input[name=reviewId]').val(reviewId)
    window.location.hash = "!edit-"+reviewId

  $('a.edit-review').click ->
    showEditModal($(@))
    return false;

  modal.on 'hidden.bs.modal',  ->
    window.location.hash = "#!"
