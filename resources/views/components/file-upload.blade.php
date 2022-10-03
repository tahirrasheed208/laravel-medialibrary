<div class="{{ $previewClasses }}">
  <img class="img-thumbnail img-preview" src="{{ $thumbnail }}">
</div>
<div>
  <span class="input-group-prepend">
    <button class="{{ $uploadButtonClasses }}" type="button"><i class="fa fa-upload mr-1"></i> {{ __('Upload') }}</button>
    <input name="{{ $name }}" type="file" class="ps-file-input d-none">
    <button class="{{ $removeButtonClasses }}" type="button"><i class="fa fa-times mr-1"></i> {{ __('Remove') }}</button>
    @if ($file)
    <input type="hidden" class="remove_file_field" name="remove_{{ $name }}" value="No">
    @endif
  </span>
</div>
<x-form-error name="{{ $name }}" />

@once
  @push('footer')
    <script src="{{ asset('assets/backend') }}/js/file-uploader.js"></script>
  @endpush
@endonce