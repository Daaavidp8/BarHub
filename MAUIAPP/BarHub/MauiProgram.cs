using BarHub.Lib;
using BarHub.Pages.Admin;
using BarHub.Pages.Camarero;
using BarHub.Pages.Login;
using BarHub.Pages.Profile;
using BarHub.Pages.Propietario;
using BarHub.ViewModel.Login;
using CommunityToolkit.Maui;

#if ANDROID
using BarHub.Platforms.Android.Shell;
#endif
using InputKit.Handlers;
using Microsoft.Extensions.Logging;
using UraniumUI;
namespace BarHub;

public static class MauiProgram
{
	public static MauiApp CreateMauiApp()
	{
		var builder = MauiApp.CreateBuilder();
		builder
			.UseMauiApp<App>()
			.ConfigureFonts(fonts =>
			{
				fonts.AddFont("OpenSans-Regular.ttf", "OpenSansRegular");
				fonts.AddFont("OpenSans-Semibold.ttf", "OpenSansSemibold");
                fonts.AddMaterialSymbolsFonts();
                fonts.AddFluentIconFonts();
                fonts.AddFontAwesomeIconFonts();
            })
            .ConfigureMauiHandlers(handlers =>
            {
#if ANDROID
                    handlers.AddHandler<Shell, CustomShellHandler>();
#endif
                handlers.AddInputKitHandlers();

            })
            .UseMauiCommunityToolkit()
            .UseUraniumUI()
		    .UseUraniumUIMaterial();

#if DEBUG
		builder.Logging.AddDebug();
#endif
        builder.Services.AddTransient<HttpClient>();
        builder.Services.AddSingleton(new Methods(ApiConstants.BaseUrl));
        builder.Services.AddTransient<Posts>();
        builder.Services.AddTransient<Login>();
        builder.Services.AddTransient<LoginViewModel>();
        builder.Services.AddTransient<ProfilePage>();
        builder.Services.AddTransient<AdminPage>();
        builder.Services.AddTransient<OwnerPage>();
        builder.Services.AddTransient<WaiterPage>();

        return builder.Build();
	}
}
