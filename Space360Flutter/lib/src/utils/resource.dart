abstract class Resource<T> {}

class Loading<T> extends Resource<T> {}

class Success<T> extends Resource<T> {
  final T data;
  Success(this.data);
}

class AppError<T> extends Resource<T> {
  final String message;
  AppError(this.message);
}
