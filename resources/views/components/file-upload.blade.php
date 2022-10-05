<div class="lm-file-upload-box">
  <div class="{{ $previewClasses }}">
    <img class="img-thumbnail" src="{{ $thumbnail }}" width="150" onclick="changeFile(this)">
  </div>
  <button class="{{ $uploadButtonClasses }}" type="button" onclick="uploadFile(this)">{{ __('Upload') }}</button>
  <input name="{{ $name }}" type="file" class="d-none" onchange="chooseFile(this)">
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
  @push('footer')
    <script src="{{ route('medialibrary.uploader') }}?ver=1.1.0"></script>
  @endpush
@endonce