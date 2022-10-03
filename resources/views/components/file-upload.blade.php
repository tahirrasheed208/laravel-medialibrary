
<div class="file-upload-box">
  <div class="{{ $previewClasses }}">
    <img class="img-thumbnail img-preview" src="{{ $thumbnail }}">
  </div>
  <span class="input-group-prepend">
    <button class="{{ $uploadButtonClasses }}" type="button"><i class="fa fa-upload mr-1"></i> {{ __('Upload') }}</button>
    <input name="{{ $name }}" type="file" class="ps-file-input d-none">
    <button class="{{ $removeButtonClasses }}" type="button"><i class="fa fa-times mr-1"></i> {{ __('Remove') }}</button>
    @if ($file)
      <input type="hidden" class="remove_file_field" name="remove_{{ $name }}" value="no">
    @endif
  </span>
</div>

@error($name)
  <span class="invalid-feedback d-block">
    <strong>{{ $message }}</strong>
  </span>
@enderror

@once
  @push('footer')
    <script src="{{ route('medialibrary.uploader') }}?ver={{ time() }}"></script>
  @endpush
@endonce