import Link from "next/link";

export default function NotFound() {
  return (
    <html>
      <body className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
        <div className="text-center max-w-md px-4">
          <span className="text-9xl font-black bg-gradient-to-r from-teal-600 to-teal-400 bg-clip-text text-transparent">
            404
          </span>
          <h1 className="text-3xl font-bold text-slate-900 mt-4 mb-4">
            Page Not Found
          </h1>
          <p className="text-slate-600 mb-8 text-lg">
            Sorry, the page you are looking for does not exist or has been
            moved.
          </p>
          <Link
            href="/"
            className="inline-flex items-center justify-center gap-2 font-semibold rounded-xl px-6 py-3 bg-teal-600 text-white hover:bg-teal-700 transition-colors"
          >
            Back to Home
          </Link>
        </div>
      </body>
    </html>
  );
}
