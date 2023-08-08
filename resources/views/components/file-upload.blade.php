<div class="lm-file-upload-box">
  <div class="{{ $previewClasses }}">
    <img class="img-thumbnail" src="{{ $thumbnail }}" width="150" onclick="changeFile(this)">
  </div>
  <button class="{{ $uploadButtonClasses }}" type="button" onclick="uploadFile(this)">{{ __('Upload') }}</button>
  <input name="{{ $name }}" type="file" class="d-none" accept="{{ $accept }}" onchange="chooseFile(this)">
  <button class="{{ $removeButtonClasses }}" type="button" onclick="removeFile(this)">{{ __('Remove') }}</button>
  @if ($file)
    <input type="hidden" name="remove_{{ $name }}" value="no">
  @endif
</div>

@error($name)
  <span class="invalid-feedback d-block">
    <strong>{{ $message }}</strong>
  </span>
@enderror

@once
  @push(config('medialibrary.stack'))
    @mediaLibraryScript
  @endpush
@endonce