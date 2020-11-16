import flagComponents from '../assets';

flagComponents.forEach((flagComponent) => {
    Shopware.Component.register(flagComponent.name, flagComponent);
});
