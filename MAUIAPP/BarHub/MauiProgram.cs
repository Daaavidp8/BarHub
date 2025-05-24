using BarHub.Lib;
using BarHub.Pages;
using BarHub.Pages.Admin;
using BarHub.Pages.Camarero;
using BarHub.Pages.Login;
using BarHub.Pages.Orders;
using BarHub.Pages.Profile;
using BarHub.Pages.Propietario;
using BarHub.Pages.Propietario.Articles;
using BarHub.Pages.Propietario.Workers;
using BarHub.Pages.Waiter;
using BarHub.Utils.UI.General;
using BarHub.ViewModel;
using BarHub.ViewModel.Admin;
using BarHub.ViewModel.Interfaces;
using BarHub.ViewModel.Login;
using BarHub.ViewModel.Order;
using BarHub.ViewModel.Owner;
using BarHub.ViewModel.Owner.Articles;
using BarHub.ViewModel.Owner.Workers;
using BarHub.ViewModel.Waiter;
using CommunityToolkit.Maui;

#if ANDROID
using BarHub.Platforms.Android.Shell;
#endif
using InputKit.Handlers;
using Maui.FreakyControls.Extensions;
using Microsoft.Extensions.Logging;
using Mopups.Hosting;
using Mopups.Interfaces;
using Mopups.Services;
using MPowerKit.VirtualizeListView;
using UraniumUI;
namespace BarHub;

public static class MauiProgram
{
    public static MauiApp CreateMauiApp()
    {
        var builder = MauiApp.CreateBuilder();
        builder
            .UseMauiApp<App>()
            .UseMauiCommunityToolkit()
            .ConfigureMopups()
            .ConfigureFonts(fonts =>
            {
                fonts.AddFont("OpenSans-Regular.ttf", "OpenSansRegular");
                fonts.AddFont("OpenSans-Semibold.ttf", "OpenSansSemibold");
                fonts.AddMaterialSymbolsFonts();
                fonts.AddFluentIconFonts();
                fonts.AddFontAwesomeIconFonts();
            })
             .UseMPowerKitListView()
             .InitializeFreakyControls()
            .ConfigureMauiHandlers(handlers =>
            {
#if ANDROID
                    handlers.AddHandler<Shell, CustomShellHandler>();
#endif
                handlers.AddInputKitHandlers();

            })
            .UseUraniumUI()
            .UseUraniumUIMaterial();

        builder.Services.AddMopupsDialogs();
#if DEBUG
        builder.Logging.AddDebug();
#endif
        builder.ConfigureServices();



        return builder.Build();
    }

    private static void ConfigureServices(this MauiAppBuilder builder)
    {
        builder.Services.AddSingleton<IContext<OrderLineViewModel>, Context<OrderLineViewModel>>();
        builder.Services.AddSingleton<IContext<WorkerViewModel>, Context<WorkerViewModel>>();
        builder.Services.AddSingleton<IContext<ArticleViewModel>, Context<ArticleViewModel>>();
        builder.Services.AddSingleton<IContext<RestaurantViewModel>, Context<RestaurantViewModel>>();
        builder.Services.AddSingleton<IContext<SectionViewModel>, Context<SectionViewModel>>();
        builder.Services.AddTransient<FunctionsUI>();
        builder.Services.AddTransient<AppShell>();
        builder.Services.AddTransient<HttpClient>();
        builder.Services.AddSingleton(new Methods(ApiConstants.BaseUrl));
        builder.Services.AddTransient<Gets>();
        builder.Services.AddTransient<Deletes>();
        builder.Services.AddTransient<Posts>();
        builder.Services.AddTransient<Puts>();
        builder.Services.AddTransient<Login>();
        builder.Services.AddTransient<LoginViewModel>();
        builder.Services.AddTransient<BarHubBaseContentPage>();
        builder.Services.AddTransient<ProfilePage>();
        builder.Services.AddSingleton<AdminViewModel>();
        builder.Services.AddTransient<AdminPage>();
        builder.Services.AddTransient<ManageRestaurantViewModel>();
        builder.Services.AddTransient<ManageRestaurant>();
        builder.Services.AddTransient<SectionsPageViewModel>();
        builder.Services.AddTransient<OwnerPage>();
        builder.Services.AddTransient<ArticlesPageViewModel>();
        builder.Services.AddTransient<ArticlesPage>();
        builder.Services.AddTransient<ManageArticleViewModel>();
        builder.Services.AddTransient<ManageArticle>();
        builder.Services.AddTransient<ManageSectionViewModel>();
        builder.Services.AddTransient<ManageSection>();
        builder.Services.AddTransient<WorkersPageViewModel>();
        builder.Services.AddTransient<WorkerViewModel>();
        builder.Services.AddTransient<ManageWorkerViewModel>();
        builder.Services.AddTransient<ManageWorker>();
        builder.Services.AddTransient<DetailsTableViewModel>();
        builder.Services.AddTransient<TableDetails>();
        builder.Services.AddTransient<WaiterViewModel>();
        builder.Services.AddTransient<WaiterPage>();
        builder.Services.AddTransient<OrderPageViewModel>();
        builder.Services.AddTransient<OrdersPage>();
    }
}
