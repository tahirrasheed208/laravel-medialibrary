<input type="hidden" name="{{ $name }}" id="{{ $name }}">
<div id="{{ $dropzone_id }}" class="dropzone needsclick mb-3">
  <div class="dz-message needsclick">
    <button type="button" class="dz-button">{{ $message }}</button>
  </div>
</div>

@once
  @push(config('medialibrary.stack'))
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <style>
      .dropzone { border: 2px dashed #0087F7; border-radius: 5px; background: white; }
      .dropzone .dz-message { font-weight: 400; }
      .dropzone .dz-message .note { font-size: 0.8em; font-weight: 200; display: block; margin-top: 1.4rem; }
      .dropzone .dz-preview .dz-image img { max-width: 100%;}
      .dz-size { display: none !important;}
    </style>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
  @endpush
@endonce

@once
  @push(config('medialibrary.stack'))
    <script>
      Dropzone.autoDiscover = false;
      var fileList = [];
    </script>
  @endpush
@endonce

@push(config('medialibrary.stack'))
  <script>
    fileList['{{ $name }}'] = [];
    new Dropzone("div#{{ $dropzone_id }}", {
      url: "{{ route('medialibrary.dropzone.upload') }}",
      addRemoveLinks: true,
      uploadMultiple: true,
      parallelUploads: {{ $max_files }},
      maxFiles: {{ $max_files }},
      maxFilesize: {{ $max_file_size }}, // MB
      acceptedFiles: "{{ config('medialibrary.accept_files') }}",
      headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      },
      init: function() {
        const thisDropzone = this;

        @if (!empty($attachments))
          let attachments = @json($attachments);

          attachments.forEach(element => {
            thisDropzone.options.addedfile.call(thisDropzone, element.mockFile);
            thisDropzone.options.thumbnail.call(thisDropzone, element.mockFile, element.url);
            thisDropzone.options.complete.call(thisDropzone, element.mockFile);
            thisDropzone.files.push(element.mockFile);
          })
        @endif

        thisDropzone.on("sending", function(file, xhr, formData) {
          formData.append("type", "{{ $name }}");
          formData.append("collection", "{{ $collection }}");

          @if ($model)
            formData.append("model", "{{ class_basename($model) }}");
            formData.append("model_id", {{ $model->id }});
          @endif
        });

        thisDropzone.on("success", function(file, serverFileName) {
          serverFileName.forEach(element => {
            if (element.file_name === file.upload.filename) {
              file.upload.filename = element.new_name;
              file.upload.media_id = element.media_id;
              fileList['{{ $name }}'].push(element.new_name);
            }
          });
        });

        thisDropzone.on("complete", function(file) {
          if (file.status === 'error') {
            return;
          }

          document.getElementById('{{ $name }}').value = fileList['{{ $name }}'].toString();
        });

        thisDropzone.on("removedfile", function(file) {
          let file_name = file.upload.filename;

          const index = fileList['{{ $name }}'].indexOf(file_name);
          if (index > -1) {
            fileList['{{ $name }}'].splice(index, 1);
            document.getElementById('{{ $name }}').value = fileList['{{ $name }}'].toString();
          }

          let xhr = new XMLHttpRequest();
          xhr.open("POST", "{{ route('medialibrary.dropzone.delete') }}");
          xhr.setRequestHeader("Accept", "application/json");
          xhr.setRequestHeader("Content-Type", "application/json");
          xhr.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");

          const data = `{
            "file_name": "${file_name}",
            "media_id": "${file.upload.media_id}"
          }`

          xhr.send(data);
        });
      }
    });
  </script>
@endpush